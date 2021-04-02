<?php
//TRCD用権限
return [
	'CONST' => [
		'ATTENDANCE_MANAGER' => 10, //勤怠管理
		'EMPLOYEE_MANAGER' => 20, //社員管理
		'PAYMENT_MANAGER' => 30, //出入金管理 
		'MESSAGE_MANAGER' => 40, //メッセージ管理
		'VEIN_INFORMATION_MANAGER' => 50, // 静脈情報管理者
		'ATTENDANCE_REQUEST_MANAGER' => 60, //勤怠申請管理
		'EXPENSE_REGISTRATION' => 70, //経費登録
		'EXPENSE_APPROVAL' => 71, //経費承認
		'EXPENSE_MANAGER' => 72, //経費管理
		'TEMPORARY_PAYMENT_MANAGER' => 80, //仮払い管理
		'ATTENDANCE_REQUEST_ONLY' => 100,//勤怠申請のみ可能
		'ATTENDANCE_READING_ONLY' => 200,//勤怠参照のみ可能
		'ADMIN' => 1000,//管理者
	],
	'LIST' => [
		1000 => '管理者',
		50 => '静脈情報管理者',
		30 => '出入金管理/TRCD管理',
		20 => '社員管理',
		10 => '勤怠管理',
		60 => '勤怠申請管理',
/*
		@terada 2020/04/10 社員一覧の登録・編集ページのチェックボックスの表示順を変更するために、順番を変更
		70 => '経費登録',
		71 => '経費承認',
		72 => '経費管理',
		80 => '仮払い管理',
*/
		40 => 'メッセージ管理',
		100 => '勤怠申請のみ',
		200 => '勤怠参照のみ',
		72 => '経費管理',
		71 => '経費承認',
		70 => '経費登録',
		80 => '仮払い管理',
	],
];
