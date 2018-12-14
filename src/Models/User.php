<?php
/**
 * User.php
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

namespace Xpressengine\Plugins\Mailing\Models;

use Xpressengine\User\Models\User as OriginUser;

/**
 * User
 *
 * @category    Mailing
 * @package     Xpressengine\Plugins\Mailing
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        http://www.xpressengine.com
 */
class User extends OriginUser
{
    /**
     * mailing log
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @deprecated since rc.8 instead use mailingLogs()
     */
    public function mailing_logs()
    {
        return $this->hasMany(Log::class, 'user_id');
    }

    /**
     * mailing log
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mailingLogs()
    {
        return $this->hasMany(Log::class, 'user_id');
    }

    /**
     * mailing
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function mailing()
    {
        return $this->hasOne(Mailing::class, 'user_id');
    }
}
