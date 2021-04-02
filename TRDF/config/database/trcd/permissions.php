<?php
// TRCD管理画面用認可
return [
	// 社員
	'read client_employees', //閲覧
	'create client_employees', //登録
	'update client_employees', //編集
	'delete client_employees', //削除
	'import client_employees', //インポート
	'export client_employees', //エクスポート
	// パスワード
	'reset password', // パスワード再設定
	// 勤怠
	'read attendances', //閲覧
	'create attendances', //登録
	'update attendances', //編集
	'delete attendances', //削除
	'request attendances', //申請
	'request others_attendances', // 他者の勤怠申請
	'cancel attendances', //申請取消
	'approve attendances', //申請承認
	'deny attendances', //申請否認
	'read attendance_aggregations', //勤怠集計閲覧
	'export attendance_aggregations', //勤怠集計エクスポート
	'access daily_attendance_page', // 日毎一覧ページへアクセス
	'create holidays', // 休日登録
	'delete holidays', // 休日削除
	// 出入金
	'read payment_histories', //履歴閲覧
	'read withdrawal_aggregations', //払出し集計閲覧
	'export withdrawal_aggregations', //払出し集計エクスポート
	// お知らせ
	'read messages', //閲覧
	'create messages', //登録
	'update messages', //編集
	'delete messages', //削除
	// TRCD端末エラーログ
	'read trcd_messages', //閲覧
	// 勤務パターン
	'read attendance_patterns', //閲覧
	'create attendance_patterns', //登録
	'update attendance_patterns', //編集
	'delete attendance_patterns', //削除
	// 休憩パターン
	'read break_types', //閲覧
	'create break_types', //登録
	'update break_types', //編集
	'delete break_types', //削除
	// 勤怠所属グループ
	'read client_groups', //閲覧
	'create client_groups', //登録
	'update client_groups', //編集
	'delete client_groups', //削除
	// 有給パターン
	'read paid_holidays', //閲覧
	'create paid_holidays', //登録
	'update paid_holidays', //編集
	'delete paid_holidays', //削除
	// 特殊勤怠パターン
	'read attendance_special_types', //閲覧
	'create attendance_special_types', //登録
	'update attendance_special_types', //編集
	'delete attendance_special_types', //削除
	// TRCD企業設定
	'update payroll_start_day', //払出初期日（締め日翌日） 更新
	'update withdraw_amount_limit_a_day', //全社員：払出上限金額 更新
	'update rounding_franction', //勤怠の切捨単位 更新
	// TRCD端末への入出金
	'manage trcd_terminal', // TRCD端末管理者
	// TRCD端末の静脈認証
	'read trcd_vein_informations', // 閲覧
	'create trcd_vein_informations', //登録
	'update trcd_vein_informations', //編集
	'delete trcd_vein_informations', //削除
	// 経費
	'read expenses', //閲覧
	'create expenses', //登録
	'update expenses', //編集
	'delete expenses', //削除
	'request expenses', //申請
	'approve expenses', //承認
	'unlock expenses', //経費概要と仮払い金払い出し後の仮払い概要のロック解除
	'update account_titles', //勘定科目設定
	'read expense_aggregations', // 経費集計閲覧
	'export expense_aggregations', // 経費集計エクスポート
	// 仮払い
	'read temporary_payments', //閲覧
	'create temporary_payments', //登録
	'update temporary_payments', //編集
	'delete temporary_payments', //削除
	'request temporary_payments', //申請
	'approve temporary_payments', //承認
	'unlock temporary_payments', //仮払い金払い出し前の仮払い概要のロック解除
	'read temporary_payment_aggregations', // 仮払い集計閲覧
	'export temporary_payment_aggregations', // 仮払い集計エクスポート
	// 経費所属グループ
	'read expense_groups', //閲覧
	'create expense_groups', //登録
	'update expense_groups', //編集
	'delete expense_groups', //削除
	// 経費所属グループ設定
	'update expense_group_settings', //編集
	// 仮払い概要候補
	'read temporary_payment_summary_candidates', //閲覧
	'create temporary_payment_summary_candidates', //登録
	'update temporary_payment_summary_candidates', //編集
	'delete temporary_payment_summary_candidates', //削除
	// 残高不足通知設定
	'update trcd_terminal_notification_settings', //編集
];
