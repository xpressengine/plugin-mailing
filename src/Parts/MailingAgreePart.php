<?php
/**
 * MailingAgreePart.php
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

namespace Xpressengine\Plugins\Mailing\Parts;

use Xpressengine\User\Parts\RegisterFormPart;

/**
 * MailingAgreePart
 *
 * @category    Mailing
 * @package     Xpressengine\Plugins\Mailing
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class MailingAgreePart extends RegisterFormPart
{
    const ID = 'mailing-agree';

    const NAME = '메일링 수신 동의';

    const DESCRIPTION = '메일링 수신의 동의를 위한 체크박스 입니다.';

    protected static $view = 'register.forms.mailing';

    /**
     * get default file
     *
     * @return string|null
     */
    public function getDefaultFile()
    {
        return plugins_path('mailing/views/register.blade.php');
    }
}
