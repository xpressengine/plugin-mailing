<h1>프로모션 메일링 설정</h1>
<p>사이트에서 제공하는 이벤트 등의 프로모션 메일링의 수신 여부를 설정할 수 있습니다.</p>
<div class="setting-card login-connect">
    <h2>수신 여부 설정</h2>
    <div class="setting-group">

        <div class="setting-group-content __xe_mailing">

            @include($plugin->view('views.user.show'))
        </div>
    </div>
</div>


{!!  app('xe.frontend')->html('mailing::update')->content("
<script>
    function updateMailing(data) {
        XE.page('".route('mailing::setting.show')."', '.__xe_mailing');
        XE.toast(data.type, data.message);
    }
</script>
")->load()  !!}
