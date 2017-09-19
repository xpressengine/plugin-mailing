<?php
/**
 *  This file is part of the Xpressengine package.
 *
 * PHP version 5
 *
 * @category
 * @package     Xpressengine\
 * @author      XE Team (khongchi) <khongchi@xpressengine.com>
 * @copyright   2000-2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Mailing\Models;

use Illuminate\Database\Eloquent\Model;


/**
     * @category
     * @package     Xpressengine\Plugins\Mailing
     * @author      XE Team (khongchi) <khongchi@xpressengine.com>
     * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
     * @link        http://www.xpressengine.com
     */
class Mailing extends Model
{
    protected $table = 'mailing';

    protected $primaryKey = 'user_id';

    public $timestamps = true;

    public function user()
    {
        $this->belongsTo(User::class, 'user_id');
    }

}
