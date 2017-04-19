<?php
namespace Xpressengine\Plugins\Mailing\Commands;

use Illuminate\Console\Command;
use Xpressengine\Plugins\Mailing\Handler;
use Xpressengine\Plugins\Mailing\Models\Mailing;

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
     * Create a new command instance.
     *
     * @param Handler $handler
     */

    public function __construct(Handler $handler)
    {
        parent::__construct();
        $this->handler = $handler;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user_id = $this->argument('user_id');

        $user = app('xe.user')->find($user_id);

        if ($this->input->isInteractive() && $this->confirm(
                "Do you will make '{$user->getDisplayName()}' user to be agreed to mailing . Do you want to execute it?"
            ) === false
        ) {
            $this->warn('Process is canceled by you.');
            return null;
        }

        $users = $this->handler->agree($user_id);
        $this->warn("finished".PHP_EOL);
    }
}
