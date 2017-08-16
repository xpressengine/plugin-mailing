# plugin-freezer

이 어플리케이션은 Xpressengine3(이하 XE3)의 플러그인입니다.

이 플러그인은 사이트에 가입된 회원들에게 프로모션 이메일의 수신 동의 여부를 입력받는 기능과 수신 동의한 회원에게 프로모션용 이메일을 전송할 수 있는 기능을 제공합니다.
또, 수신 동의한 회원들에게 재확인 이메일을 보낼 수 있으며, 재확인 이메일을 수신한 회원은 이메일에 포함된 링크를 클릭하여 수신 동의를 철회할 수 있습니다.

> 이 플러그인을 사용하기 위해서는 먼저 [이메일 전송 설정](https://laravel.kr/docs/5.1/mail)이 되어있어야 합니다.

## Features

- 회원 가입시 프로모션용 이메일 수신 동의 여부를 입력받습니다.
- 기존회원은 개인 설정 페이지에서 프로모션 이메일의 수신 동의 여부를 설정할 수 있습니다.
- 콘솔명령을 사용하여 수신동의한 회원들에게 수신동의 재확인 이메일을 전송할 수 있습니다

## Installation

이 플러그인을 사용하려면 먼저 XE3가 설치돼 있어야 하며, 플러그인을 XE3에 설치를 해야 합니다.

### XE3 자료실을 사용하여 설치

XE3 자료실을 통해 이 플러그인을 설치할 수 있습니다. 자세한 설치 방법은 자료실에서 볼 수 있습니다.

### git clone을 사용하여 설치

1. 설치된 XE3의 `/plugins` 디렉토리에서 아래의 명령을 실행합니다.
	```
	$ git clone https://github.com/xpressengine/plugin-mailing.git ./mailing
	```
2. 설치된 디렉토리로 이동한 다음, `composer dump`를 실행합니다.
	```
	$ cd ./mailing
	$ composer dump
	```
3. `사이트관리페이지 > 플러그인 > 플러그인 목록` 페이지에서 '휴면계정 관리' 플러그인을 활성화합니다.

### Configuration

플러그인 실행시 적용되는 기본 설정은 `/plugins/mailing/config.php`에 저장되어 있습니다. 만약 기본 설정을 변경해서 사용하고 싶은 경우, `/config/production/services.php`에 `mailing` 항목을 생성하고, 원하는 설정을 변경하면 됩니다. 

`/config/production/services.php`의 `mailing` 항목에 지정한 설정은 기본설정을 덮어 씌웁니다.

```
// config/production/services.php

<?php
return [
  'mailing' => [
    'reconfirm_timer' => 365, // 수신동의 재확인 이메일 전송 대상을 1년(마지막 동의후 1년이상 경과한 회원)으로 변경 
  ]
];
```

위 코드는 신동의 재확인 이메일 전송 기준을 2년(기본)에서 1년으로 변경하는 코드입니다. 이 설정 이외에도 많은 설정이 존재하며, 위와 같은 방식으로 변경할 수 있습니다.



### Usage

이 플러그인은 콘솔 명령어를 통해 작동합니다.

#### 수신동의 재확인 이메일 

지정된 기간 이전에 수신동의한 사용자에게 재확인 이메일을 전송합니다. 
아래의 명령을 수동으로 실행하거나 `crontab` 또는 [스케쥴러](https://laravel.com/docs/5.1/scheduling)를 사용하여 하루에 1회씩 실행되도록 하십시오.

`php artisan mailing:reconfirm` 명령을 사용하십시오.

```
$ php artisan mailing:reconfirm

 Emails will be sent to 1 users. Do you want to execute it? (yes/no) [no]:
 > yes

[2017.08.16 17:31:50] Emails were sent to 1 users to reconfirm mailing.
```

#### 특정 회원의 수신동의 처리하기

특정 회원의 프로모션 이메일 수신 여부를 허용(동의) 상태로 지정합니다.

`$ php artisan mailing:agree [USER_ID]` 명령을 사용하십시오.

```
$ $ php artisan mailing:agree aa972e8b-6a73-459a-af18-22e7991d43ad
  
   Do you will make 'khongchi' user to be agreed to mailing . Do you want to execute it? (yes/no) [no]:
   > yes
  
  finished
```






