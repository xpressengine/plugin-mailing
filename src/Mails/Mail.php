<?php
/**
 * Mail.php
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

namespace Xpressengine\Plugins\Mailing\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Xpressengine\Plugins\Mailing\Models\User;

/**
 * Mail
 *
 * @category    Mailing
 * @package     Xpressengine\Plugins\Mailing
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class Mail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Mail constructor.
     *
     * @param User $user user model
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * build
     *
     * @return Mail
     */
    public function build()
    {
        return $this->view('mailing::views.email')
            ->to($this->user->email, $this->user->getDisplayName());
    }
}
