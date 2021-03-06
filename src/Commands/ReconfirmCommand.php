<?php
/**
 * ReconfirmCommand.php
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

namespace Xpressengine\Plugins\Mailing\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Xpressengine\Plugins\Mailing\Handler;

/**
 * ReconfirmCommand
 *
 * @category    Mailing
 * @package     Xpressengine\Plugins\Mailing
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class ReconfirmCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'mailing:reconfirm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send mail to the users who agreed to mailing for re-confirm.';
    /**
     * @var Handler
     */
    protected $handler;

    /**
     * ReconfirmCommand constructor.
     *
     * @param Handler $handler handler
     */
    public function __construct(Handler $handler)
    {
        parent::__construct();
        $this->handler = $handler;
    }

    /**
     * Execute the console command.
     *
     * @return mixed|void
     */
    public function handle()
    {
        $users = $this->handler->choose();

        $count = $users->count();
        $now = Carbon::now();

        if ($count === 0) {
            $this->warn("[{$now->format('Y.m.d H:i:s')}] No users to be notified about the agreement to mailing.");
            return;
        }

        if ($this->input->isInteractive() && $this->confirm(
            // x 명의 회원에게 이메일을 보내려 합니다. 실행하시겠습니까?
            "Emails will be sent to $count users. Do you want to execute it?"
        ) === false) {
            $this->warn('Process is canceled by you.');
            return null;
        }
        $count = $this->handler->reconfirm($users);

        $this->warn("[{$now->format('Y.m.d H:i:s')}] Emails were sent to $count users to reconfirm mailing." . PHP_EOL);
    }
}
