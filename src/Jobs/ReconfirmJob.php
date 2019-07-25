<?php
/**
 * ReconfirmJob.php
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

namespace Xpressengine\Plugins\Mailing\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Xpressengine\Plugins\Mailing\Handler;

/**
 * ReconfirmJob
 *
 * @category    Mailing
 * @package     Xpressengine\Plugins\Mailing
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class ReconfirmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string|array
     */
    private $user_ids;

    /**
     * ReconfirmJob constructor.
     *
     * @param array|string $user_ids user id or user ids
     */
    public function __construct($user_ids)
    {
        $this->user_ids = $user_ids;
    }

    /**
     * handle
     *
     * @param Handler $handler handler
     *
     * @return void
     * @throws \Exception
     */
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
