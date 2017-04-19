<h1>이메일 수신거부 신청</h1>
<p>수신거부를 할 경우 이메일로 발송되는 이벤트 등 프로모션 알림을 받을 수 없습니다</p>

<form action="{{ route('mailing::deny.update', ['user_id' => $user_id]) }}" method="POST">
    <input type="hidden" name="status" value="denied">
    <input type="hidden" name="token" value="{{ $token }}">
    {{ csrf_field() }}
    {{ method_field('put') }}
    <button type="submit" class="xe-btn xe-btn-primary xe-btn-lg">수신거부 신청</button>
</form>

