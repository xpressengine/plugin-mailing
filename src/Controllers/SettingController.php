<?php
/**
 *  This file is part of the Xpressengine package.
 *
 * PHP version 5
 *
 * @category
 * @package     Xpressengine\Plugins\Mailing
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Mailing\Controllers;

use App\Http\Controllers\Controller as Origin;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Mailing\Handler;
use Xpressengine\Plugins\Mailing\Models\Mailing;
use Xpressengine\Plugins\Mailing\Plugin;

/**
 * @category    Mailing
 * @package     Xpressengine\Plugins\Mailing\Controllers
 * @author      XE Team (developers) <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class SettingController extends Origin
{
    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * SocialLoginController constructor.
     *
     * @param \Xpressengine\Plugins\Mailing\Plugin $plugin
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function index(Request $request)
    {
        $mailing = $this->getMailing($request);
        $plugin = $this->plugin;


        app('xe.frontend')->js('assets/vendor/bootstrap/js/bootstrap.min.js')->appendTo('head')->load();
        app('xe.frontend')->js(
            [
                'assets/core/xe-ui-component/js/xe-page.js',
                'assets/core/xe-ui-component/js/xe-form.js'
            ]
        )->load();
        return view($this->plugin->view('views.user.index'), compact('plugin', 'mailing'));
    }

    public function show(Request $request)
    {
        $mailing = $this->getMailing($request);
        $plugin = $this->plugin;

        return apiRender($this->plugin->view('views.user.show'), compact('plugin', 'mailing'));
    }

    public function update(Request $request, Handler $handler)
    {

        $this->validate($request, [
            'status' => 'required',
        ]);

        $status = $request->get('status');

        if($status !== 'agreed') {
            $status = 'denied';
        }

        \DB::beginTransaction();
        try {
            if($status === 'agreed') {
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
     * @param Request $request
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
