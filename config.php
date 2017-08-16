<?php

return [
    'reconfirm_timer' => 730, // 수신동의 재확인 타이머, 동의한 날짜로부터의 경과일
    'scheduled_at' => '6:00', // 메일링 재수신동의 메일 전송 배치 작업 시작시점 (6:00 => 매일 6시)
    'queue_size' => 5,
    'queue' => [
        'reconfirm' => 'sync',
    ],
    'email' => [
        'reconfirm' => ['subject' => '이벤트 등 프로모션 알림 메일 수신 재확인.', 'content' => function($user, $type, $config) {
            return "{$user->getDisplayName()}님은 {$user->mailing->updatedAt->format('Y년 m월 d일')}에 이벤트 등 프로모션 알림 메일 수신에 동의했습니다. <br>이벤트 등 프로모션 관련 안내 메일 수신을 중단하기 원하시면 개인정보 > 이메일주소 화면에서 수신 동의 체크를 해제하시면 됩니다. <br>앞으로도 안내 메일을 수신하고자 하시는 회원님은 별도의 조치를 하지 않으셔도 됩니다. 감사합니다.";
        }],
        'agree' => ['subject' => '이벤트 등 프로모션 메일 수신 동의 알림.', 'content' => function($user, $type, $config) {
            $url = route('mailing::deny.show', ['user_id' => $user->id, 'status'=>'denied', 'token'=>$user->mailing->denyToken]);
            return "{$user->getDisplayName()}님은 {$user->mailing->updatedAt->format('Y년 m월 d일')}에 이벤트 등 프로모션 알림 메일 수신에 동의했습니다. <br>If you don't want to receive this email anymore <a href='{$url}'>click here</a>.";
        }],
        'deny' => ['subject' => '이벤트 등 프로모션 메일 수신 거부 처리 알림.', 'content' => function($user, $type, $config) {
            return "{$user->getDisplayName()}님, 이벤트 등 프로모션 알림 메일 수신 거부 처리가 완료되었습니다. 감사합니다.";
        }],
    ]
];
