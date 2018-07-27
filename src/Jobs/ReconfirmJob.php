<?php
namespace Xpressengine\Plugins\Mailing\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Xpressengine\Plugins\Mailing\Handler;

/**
 * @category
 * @package     Xpressengine\Plugins\Store\Jobs
 * @author      XE Team (khongchi) <khongchi@xpressengine.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class ReconfirmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string|array
     */
    private $user_ids;

    /**
     * FreezeJob constructor.
     *
     * @param string|array $user_ids
     * @param $type
     */
    public function __construct($user_ids)
    {
        $this->user_ids = $user_ids;
    }

    public function handle(Handler $handler)
    {
        DB::beginTransaction();
        try {
            $handler->reconfirmUser($this->user_ids);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
    }
}
