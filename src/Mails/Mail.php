<?php
/**
 * Mail.php
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
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        http://www.xpressengine.com
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
