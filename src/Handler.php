<?php
/**
 *  This file is part of the Xpressengine package.
 *
 * PHP version 5
 *
 * @category
 * @package     Xpressengine\
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Mailing;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Xpressengine\Plugins\Mailing\Exceptions\InvalidTokenException;
use Xpressengine\Plugins\Mailing\Jobs\ReconfirmJob;
use Xpressengine\Plugins\Mailing\Models\Log;
use Xpressengine\Plugins\Mailing\Models\Mailing;
use Xpressengine\Plugins\Mailing\Models\User;
use Xpressengine\User\UserHandler;

/**
 * @category
 * @package     Xpressengine\Plugins\Freezer
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class Handler
{
    use DispatchesJobs;

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var UserHandler
     */
    protected $handler;

    /**
     * Handler constructor.
     *
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin, UserHandler $handler)
    {
        $this->plugin = $plugin;
        $this->handler = $handler;
    }

    public function config($field = null, $default = null)
    {
        $config = $this->plugin->config();
        return array_get($config, $field, $default);
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
        $candidates = User::whereHas('mailing', function($q) use($eventDate) {
            return $q->where('status', 'agreed')->where('updatedAt', '<', $eventDate);
        })->with(['mailing_logs' => function ($q) {
            return $q->orderBy('createdAt', 'desc');
        }])->get();

        foreach ($candidates as $user) {
            $latestLog = $user->mailing_logs->first();
            if(data_get($latestLog, 'action') !== 'reconfirm') {
                $users->add($user);
            }
        }
        return $users;
    }

    public function reconfirm($users = null)
    {
        $size = $this->config('queue_size', 1);

        if($users === null) {
            $users = $this->choose();
        }

        $user_ids = [];
        foreach ($users as $user) {
            $user_ids[] = $user->id;

            if(count($user_ids) === $size) {
                $this->dispatch(new ReconfirmJob($user_ids));
                $user_ids = [];
            }
        }
        if(count($user_ids)) {
            $this->dispatch(new ReconfirmJob($user_ids));
        }

        return $users->count();
    }

    public function reconfirmUser($user_ids)
    {
        if(is_string($user_ids)) {
            $user_ids = [$user_ids];
        }

        $users = User::with('mailing')->findMany($user_ids);

        foreach ($users as $user) {
            try {
                $this->sendEmail($user, 'reconfirm');
            } catch (\Exception $e) {
                $this->logging($user->id, 'reconfirm', ['message'=>$e->getMessage()], 'failed');
                throw $e;
            }
            $this->logging($user->id, 'reconfirm');
        }
    }

    public function agree($user_id)
    {
        try {
            $user = User::find($user_id);

            $mailing = Mailing::findOrNew($user_id);
            $mailing->userId = $user_id;
            $mailing->status = 'agreed';
            $mailing->denyToken = app('xe.keygen')->generate();
            $mailing->save();

            $user->mailing = $mailing;

            $this->sendEmail($user, 'agree');
        } catch (\Exception $e) {
            $this->logging($user_id, 'agree', ['message'=>$e->getMessage()], 'failed');
            throw $e;
        }
        $this->logging($user_id, 'agree');
    }

    public function deny($user_id, $token = null)
    {
        try {
            $user = $this->handler->find($user_id);

            if($token !== null) {
                $mailing = Mailing::where('denyToken', $token)->where('userId', $user_id)->first();
                if($mailing === null) {
                    throw new InvalidTokenException();
                }
            } else {
                $mailing = Mailing::findOrNew($user_id);
                $mailing->userId = $user_id;
            }

            $mailing->status = 'denied';
            $mailing->save();



            $this->sendEmail($user, 'deny');
        } catch (\Exception $e) {
            $this->logging($user_id, 'deny', ['message'=>$e->getMessage()], 'failed');
            throw $e;
        }
        $this->logging($user_id, 'deny');
    }

    protected function logging($user_id, $action, $content = [], $result = 'successd')
    {
        // action = agreed, denied, reconfirmed
        $log = new Log();
        $log->userId = $user_id;
        $log->action = $action;
        $log->result = $result;
        $log->content = $content;
        $log->save();
    }

    protected function sendEmail($user, $type)
    {
        $subject = $this->config("email.$type.subject");

        $content = $this->config("email.$type.content");

        $content = $content($user, $type, $this->config());

        // type = freeze, delete, unfreeze, notify
        $view = $this->plugin->view('views.email') ;
        $emailAddr = $user->email;
        if($emailAddr) {
            app('mailer')->queue(
                $view,
                compact('content'),
                function ($message) use ($emailAddr, $subject) {
                    $message->to($emailAddr)->subject($subject);
                },
                'sync'
            );
        }
    }

}