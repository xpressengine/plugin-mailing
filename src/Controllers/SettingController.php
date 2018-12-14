<?php
/**
 * SettingController.php
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

namespace Xpressengine\Plugins\Mailing\Controllers;

use App\Http\Controllers\Controller as Origin;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Mailing\Handler;
use Xpressengine\Plugins\Mailing\Models\Mailing;
use Xpressengine\Plugins\Mailing\Plugin;

/**
 * SettingController
 *
 * @category    Mailing
 * @package     Xpressengine\Plugins\Mailing
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        http://www.xpressengine.com
 */
class SettingController extends Origin
{
    /**
     * index
     *
     * @param Request $request request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $mailing = $this->getMailing($request);

        app('xe.frontend')->js('assets/vendor/bootstrap/js/bootstrap.min.js')->appendTo('head')->load();
        app('xe.frontend')->js(
            [
                'assets/core/xe-ui-component/js/xe-page.js',
                'assets/core/xe-ui-component/js/xe-form.js'
            ]
        )->load();
        return view('mailing::views.user.index', compact('mailing'));
    }

    /**
     * show
     *
     * @param Request $request request
     *
     * @return mixed
     */
    public function show(Request $request)
    {
        $mailing = $this->getMailing($request);

        return api_render('mailing::views.user.show', compact('mailing'));
    }

    /**
     * update
     *
     * @param Request $request request
     * @param Handler $handler handler
     *
     * @return mixed
     * @throws \Exception
     */
    public function update(Request $request, Handler $handler)
    {

        $this->validate($request, [
            'status' => 'required',
        ]);

        $status = $request->get('status');

        if ($status !== 'agreed') {
            $status = 'denied';
        }

        \DB::beginTransaction();
        try {
            if ($status === 'agreed') {
                $handler->agree($request->user()->id);
            } else {
                $handler->deny($request->user()->id);
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
        \DB::commit();

        return app('xe.presenter')->makeApi(
            [
                'type' => 'success',
                'message' => '수정되었습니다.'
            ]
        );
    }

    /**
     * getMailing
     *
     * @param Request $request request
     *
     * @return Mailing
     */
    protected function getMailing(Request $request)
    {
        $user = $request->user();
        $mailing = Mailing::where('user_id', $user->id)->first();

        if ($mailing === null) {
            $mailing = new Mailing();
            $mailing->user_id = $user->id;
            $mailing->status = 'denied';
            return $mailing;
        }

        return $mailing;
    }
}
