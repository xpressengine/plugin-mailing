<?php
namespace Xpressengine\Plugins\Mailing;

use Illuminate\Database\Schema\Blueprint;
use Route;
use Schema;
use Xpressengine\Plugin\AbstractPlugin;
use Xpressengine\Plugins\Mailing\Commands\AgreeCommand;
use Xpressengine\Plugins\Mailing\Commands\ReconfirmCommand;

class Plugin extends AbstractPlugin
{
    /**
     * 이 메소드는 활성화(activate) 된 플러그인이 부트될 때 항상 실행됩니다.
     *
     * @return void
     */
    public function boot()
    {
        $this->register();
        $this->route();

        app('xe.register')->push(
            'user/settings/section',
            'mailing@agreement',
            [
                'title' => '프로모션 메일링 설정',
                'content' => function () {
                    return app()->call('Xpressengine\Plugins\Mailing\Controllers\SettingController@index');
                }
            ]
        );

        intercept('XeUser@getRegisterForms', 'mailing@form', function($target, $token) {

            $forms = $target($token);

            $updated = [];
            foreach ($forms as $id => $form) {
                $updated[$id] = $form;
                if($id === 'agreements') {
                    $updated['mailing@agreement'] = function ($token) {
                        return '<div class="xe-input-group">
                                    <label class="xe-label">
                                        <input type="checkbox" name="agree_mailing" value="on">
                                        <span class="xe-input-helper"></span>
                                        <span class="xe-label-text">
                                            이벤트 및 프로모션 이메일을 수신합니다.
                                        </span>
                                    </label>
                                </div>';
                    };
                }
            }
            return $updated;
        });

        intercept('XeUser@create', 'mailing@create', function($target, $data, $token = null) {

            $agree = array_get($data, 'agree_mailing');

            $user = $target($data, $token);

            if($agree === 'on') {
                app('mailing::handler')->agree($user->id);
            }
            return $user;
        });




    }

    protected function register()
    {
        app()->singleton(
            ['mailing::handler' => Handler::class],
            function ($app) {
                $proxyClass = app('xe.interception')->proxy(Handler::class, 'Mailing');
                return new $proxyClass($this, app('xe.user'));
            }
        );

        // register commands
        app()->singleton(
            'mailing::command.reconfirm',
            function ($app) {
                return new ReconfirmCommand(app('mailing::handler'));
            }
        );

        app()->singleton(
            'mailing::command.agree',
            function ($app) {
                return new AgreeCommand(app('mailing::handler'));
            }
        );


        $commands = ['mailing::command.reconfirm', 'mailing::command.agree'];
        app('events')->listen(
            'artisan.start',
            function ($artisan) use ($commands) {
                $artisan->resolveCommands($commands);
            }
        );
    }

    public function config() {
        return config('services.mailing');
    }

    protected function route()
    {
        Route::fixed(static::getId(), function(){
            Route::group(['namespace'=>'Xpressengine\Plugins\Mailing\Controllers'], function(){
                Route::get('users/{user_id}/mailing', ['as' => 'mailing::deny.show', 'uses' => 'Controller@show']);
                Route::put('users/{user_id}/mailing', ['as' => 'mailing::deny.update', 'uses' => 'Controller@update']);

                Route::get('user/setting', ['as' => 'mailing::setting.show', 'uses' => 'SettingController@show', 'middleware'=> 'auth']);
                Route::put('user/setting', ['as' => 'mailing::setting.update', 'uses' => 'SettingController@update', 'middleware'=> 'auth']);
            });
        });
    }

    /**
     * 플러그인이 활성화될 때 실행할 코드를 여기에 작성한다.
     *
     * @param string|null $installedVersion 현재 XpressEngine에 설치된 플러그인의 버전정보
     *
     * @return void
     */
    public function activate($installedVersion = null)
    {
        // implement code
    }

    /**
     * 플러그인을 설치한다. 플러그인이 설치될 때 실행할 코드를 여기에 작성한다
     *
     * @return void
     */
    public function install()
    {
        if(!Schema::hasTable('mailing')) {
            Schema::create('mailing', function (Blueprint $table) {
                $table->engine = "InnoDB";

                //user_id VARCHAR(36) PRIMARY KEY NOT NULL,
                $table->string('userId', 36);
                $table->char('status', 20);
                $table->string('denyToken', 36)->nullable();
                $table->timestamp('createdAt')->index();
                $table->timestamp('updatedAt')->index();
                $table->primary('userId');

            });
        }

        if(!Schema::hasTable('mailing_log')) {
            Schema::create('mailing_log', function (Blueprint $table) {
                $table->engine = "InnoDB";

                $table->increments('id');
                $table->string('userId', 36);
                $table->string('action', 20); //
                $table->string('result', 20); // successd, failed
                $table->string('content');
                $table->timestamp('createdAt')->index();
                $table->timestamp('updatedAt');
                $table->index('userId');
            });
        }



    }

    /**
     * 해당 플러그인이 설치된 상태라면 true, 설치되어있지 않다면 false를 반환한다.
     * 이 메소드를 구현하지 않았다면 기본적으로 설치된 상태(true)를 반환한다.
     *
     * @return boolean 플러그인의 설치 유무
     */
    public function checkInstalled()
    {
        return Schema::hasTable('mailing') && Schema::hasTable('mailing_log');
    }

    /**
     * 플러그인을 업데이트한다.
     *
     * @return void
     */
    public function update()
    {
    }

    /**
     * 해당 플러그인이 최신 상태로 업데이트가 된 상태라면 true, 업데이트가 필요한 상태라면 false를 반환함.
     * 이 메소드를 구현하지 않았다면 기본적으로 최신업데이트 상태임(true)을 반환함.
     *
     * @return boolean 플러그인의 설치 유무,
     */
    public function checkUpdated()
    {
        return true;
    }
}