<form action="{{ route('mailing::setting.update') }}" method="post" data-submit="xe-ajax" data-callback="updateMailing">
    @if($mailing->status === 'agreed')
        <input type="hidden" name="status" value="denied">
        <div class="setting-left on">
            프로모션 이메일을 받고 있습니다.
        </div>
        <div class="setting-right">
            <button type="submit" onclick="$(this).button('loading');" data-loading-text="저장중.." data-link="{{ route("mailing::setting.update") }}" class="__xe_mailingDeny xe-btn xe-btn-danger">
                수신 거부합니다
            </button>
        </div>
    @else
        <input type="hidden" name="status" value="agreed">
        <div class="setting-left">
            프로모션 이메일을 받지 않고 있습니다.
        </div>
        <div class="setting-right">
            <button type="submit" onclick="$(this).button('loading');" data-loading-text="저장중.." data-link="{{ route("mailing::setting.update") }}" class="__xe_mailingAgree xe-btn xe-btn-primary">
                수신 받겠습니다
            </button>
        </div>
    @endif
</form>
