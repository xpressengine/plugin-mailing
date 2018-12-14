<?php
/**
 * AgreeCommand.php
 *
 * This file is part of the Xpressengine package.
 *
 * PHP version 5
 *
 * @category    Mailing
 * @package     Xpressengine\Plugins\Mailing
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Mailing\Commands;

use Illuminate\Console\Command;
use Xpressengine\Plugins\Mailing\Handler;
use Xpressengine\Plugins\Mailing\Models\Mailing;

/**
 * AgreeCommand
 *
 * @category    Mailing
 * @package     Xpressengine\Plugins\Mailing
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        http://www.xpressengine.com
 */
class AgreeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'mailing:agree {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'set mailing agreement to true for the user';

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * AgreeCommand constructor.
     *
     * @param Handler $handler handler
     */
    public function __construct(Handler $handler)
    {
        parent::__construct();
        $this->handler = $handler;
    }

    /**
     * handle
     *
     * @return void|null
     * @throws \Exception
     */
    public function handle()
    {
        $user_id = $this->argument('user_id');

        $user = app('xe.user')->find($user_id);

        if ($this->input->isInteractive() && $this->confirm(
            "Do you will make '{$user->getDisplayName()}' user to be agreed to mailing . Do you want to execute it?"
        ) === false) {
            $this->warn('Process is canceled by you.');
            return null;
        }

        $users = $this->handler->agree($user_id);
        $this->warn("finished" . PHP_EOL);
    }
}
