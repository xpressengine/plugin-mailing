<?php
/**
 * MailingAgreePart.php
 *
 * PHP version 7
 *
 * @category    Mailing
 * @package     Xpressengine\Plugins\Mailing
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER Corp. <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Mailing\Parts;

use Xpressengine\User\Parts\RegisterFormPart;

class MailingAgreePart extends RegisterFormPart
{
    const ID = 'mailing-agree';

    const NAME = '메일링 수신 동의';

    const DESCRIPTION = '메일링 수신의 동의를 위한 체크박스 입니다.';

    protected static $view = 'register.forms.mailing';

    public function getDefaultFile()
    {
        return plugins_path('mailing/views/register.blade.php');
    }
}
