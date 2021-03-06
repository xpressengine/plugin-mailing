<?php
/**
 * Handler.php
 *
 * This file is part of the Xpressengine package.
 *
 * PHP version 7
 *
 * @category    Mailing
 * @package     Xpressengine\Plugins\Mailing
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Mailing;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Xpressengine\Plugins\Mailing\Exceptions\InvalidTokenException;
use Xpressengine\Plugins\Mailing\Jobs\ReconfirmJob;
use Xpressengine\Plugins\Mailing\Mails\Mail;
use Xpressengine\Plugins\Mailing\Models\Log;
use Xpressengine\Plugins\Mailing\Models\Mailing;
use Xpressengine\Plugins\Mailing\Models\User;

/**
 * Handler
 *
 * @category    Mailing
 * @package     Xpressengine\Plugins\Mailing
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class Handler
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * Handler constructor.
     *
     * @param array $config config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * config
     *
     * @param null|string $field   field
     * @param null|mixed  $default default
     *
     * @return mixed
     */
    public function config($field = null, $default = null)
    {
        return array_get($this->config, $field, $default);
    }

    /**
     * choose users for reconfirm
     *
     * @return Collection
     */
    public function choose()
    {
        $users = new Collection();

        $duration = $this->config('reconfirm_timer');
        $eventDate = Carbon::now()->subDays($duration);

        // 동의한 후보 중에 reconfirm을 보내지 않은 후보 선별하기
        $candidates = User::whereHas('mailing', function ($q) use ($eventDate) {
            return $q->where('status', 'agreed')->where('updated_at', '<', $eventDate);
        })->with(['mailingLogs' => function ($q) {
            return $q->orderBy('created_at', 'desc');
        }])->get();

        foreach ($candidates as $user) {
            $latestLog = $user->mailingLogs->first();
            if (data_get($latestLog, 'action') !== 'reconfirm') {
                $users->add($user);
            }
        }
        return $users;
    }

    /**
     * reconfirm
     *
     * @param null|User $users users
     *
     * @return int
     */
    public function reconfirm($users = null)
    {
        $size = $this->config('queue_size', 1);

        if ($users === null) {
            $users = $this->choose();
        }

        $user_ids = [];
        foreach ($users as $user) {
            $user_ids[] = $user->id;

            if (count($user_ids) === $size) {
                ReconfirmJob::dispatch($user_ids);
                $user_ids = [];
            }
        }
        if (count($user_ids)) {
            ReconfirmJob::dispatch($user_ids);
        }

        return $users->count();
    }

    /**
     * reconfirm user
     *
     * @param array|string $user_ids user id or user ids
     *
     * @return void
     * @throws \Exception
     */
    public function reconfirmUser($user_ids)
    {
        if (is_string($user_ids)) {
            $user_ids = [$user_ids];
        }

        $users = User::with('mailing')->findMany($user_ids);

        foreach ($users as $user) {
            try {
                $this->sendEmail($user, 'reconfirm');
            } catch (\Exception $e) {
                $this->logging($user->id, 'reconfirm', ['message' => $e->getMessage()], 'failed');
                throw $e;
            }
            $this->logging($user->id, 'reconfirm');
        }
    }

    /**
     * agree
     *
     * @param string $user_id user id
     *
     * @return void
     * @throws \Exception
     */
    public function agree($user_id)
    {
        try {
            $user = User::find($user_id);

            $mailing = Mailing::findOrNew($user_id);
            $mailing->user_id = $user_id;
            $mailing->status = 'agreed';
            $mailing->deny_token = app('xe.keygen')->generate();
            $mailing->save();

            $user->mailing = $mailing;

            $this->sendEmail($user, 'agree');
        } catch (\Exception $e) {
            $this->logging($user_id, 'agree', ['message' => $e->getMessage()], 'failed');
            throw $e;
        }
        $this->logging($user_id, 'agree');
    }

    /**
     * deny
     *
     * @param string      $user_id user id
     * @param null|string $token   token
     *
     * @return void
     * @throws \Exception
     */
    public function deny($user_id, $token = null)
    {
        try {
            $user = User::find($user_id);

            if ($token !== null) {
                $mailing = Mailing::where('deny_token', $token)->where('user_id', $user_id)->first();
                if ($mailing === null) {
                    throw new InvalidTokenException();
                }
            } else {
                $mailing = Mailing::findOrNew($user_id);
                $mailing->user_id = $user_id;
            }

            $mailing->status = 'denied';
            $mailing->save();

            $this->sendEmail($user, 'deny');
        } catch (\Exception $e) {
            $this->logging($user_id, 'deny', ['message' => $e->getMessage()], 'failed');
            throw $e;
        }
        $this->logging($user_id, 'deny');
    }

    /**
     * logging
     *
     * @param string $user_id user id
     * @param string $action  action
     * @param array  $content content
     * @param string $result  result
     *
     * @return void
     */
    protected function logging($user_id, $action, $content = [], $result = 'successd')
    {
        // action = agreed, denied, reconfirmed
        $log = new Log();
        $log->user_id = $user_id;
        $log->action = $action;
        $log->result = $result;
        $log->content = $content;
        $log->save();
    }

    /**
     * send email
     *
     * @param User   $user user model
     * @param string $type type
     *
     * @return void|bool
     */
    protected function sendEmail($user, $type)
    {
        if (!$user->email) {
            return false;
        }

        $subject = $this->config("email.$type.subject");
        $content = call_user_func($this->config("email.$type.content"), $user, $type, $this->config());

        $message = (new Mail($user))->subject($subject)->with('content', $content);
        app('mailer')->queue($message);
    }
}
