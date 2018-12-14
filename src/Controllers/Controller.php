<?php
/**
 * Controller.php
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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\Mailing\Exceptions\InvalidTokenException;
use Xpressengine\Plugins\Mailing\Handler;
use Xpressengine\Plugins\Mailing\Models\Mailing;
use Xpressengine\Plugins\Mailing\Plugin;

/**
 * Controller
 *
 * @category    Mailing
 * @package     Xpressengine\Plugins\Mailing
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2015 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPL-2.1
 * @link        http://www.xpressengine.com
 */
class Controller extends Origin
{
    /**
     * show
     *
     * @param Request $request request
     * @param string  $user_id user id
     *
     * @return mixed
     */
    public function show(Request $request, $user_id)
    {
        $this->validate($request, [
            'status' => 'required',
            'token' => 'required'
        ]);

        $status = $request->get('status');
        $token = $request->get('token');

        $mailing = Mailing::where('user_id', $user_id)->where('status', 'agreed')->where('deny_token', $token)->first();

        if ($mailing === null) {
            throw new HttpException('400', '토큰정보가 틀렸거나 만료된 토큰입니다.');
        }

        return app('xe.presenter')->make('mailing::views.deny', compact('user_id', 'token'));
    }

    /**
     * update
     *
     * @param Request $request request
     * @param Handler $handler handler
     * @param string  $user_id user id
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function update(Request $request, Handler $handler, $user_id)
    {
        $this->validate($request, [
            'status' => 'required',
            'token' => 'required'
        ]);

        $status = $request->get('status');
        $token = $request->get('token');

        if ($status !== 'denied') {
            throw new HttpException('400', '잘못된 입력이 있습니다.');
        }

        \DB::beginTransaction();
        try {
            $handler->deny($user_id, $token);
        } catch (InvalidTokenException $e) {
            \DB::rollBack();
            throw new HttpException('400', '토큰정보가 틀렸거나 만료된 토큰입니다.');
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
        \DB::commit();

        return redirect()->to('/')->with(['alert' => ['type' => 'success', 'message' => '처리 완료되었습니다.']]);
    }
}
