<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
Route::get('/', function () {
		return view('welcome');
});
*/

// setup.exeダウンロード用（暫定）
Route::get('doraever/application/download/latest', 'Api\ApplicationVersionsController@download_latest');

//Auth::routes();
// ==================== SuperAdmin管理画面 ====================
Route::group(['prefix' => 'superadmin'], function() {
	// SuperAdmin ログイン
	Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
	Route::post('login', 'Auth\LoginController@login');

	// SuperAdmin 登録
	Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
	Route::post('register', 'Auth\RegisterController@register');

	// SuperAdmin パスワード
	Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
	Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
	Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
	Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

});

Route::group(['middleware' => 'auth:web', 'prefix' => 'superadmin'], function () {
	// SuperAdmin ホーム
	Route::get('/', 'HomeController@index')->name('home');
	//Route::get('/home', 'HomeController@index')->name('home');
	//Route::get('/', function () { return view('index'); });
	//Route::get('home', function() { return redirect(route('home')); });
	// SuperAdmin ログアウト
	Route::post('logout', 'Auth\LoginController@logout')->name('logout');

	// 取引先
	Route::get('/companies', 'CompaniesController@index');
	Route::get('/companies/create', [
		'uses' => 'CompaniesController@create',
		'as' => 'companies.create'
	]);
	Route::post('companies', [
			'uses' => 'CompaniesController@store',
			'as' => 'companies.store'
	]);
	// 企業
	//Route::get('/client_create', 'ClientController@index');
	//Route::post('/client_create/create', 'ClientController@create');
	//Route::get('/client_create/success', 'ClientController@success');
	Route::get('/clients', 'ClientController@index')->name('clients.index');
	Route::get('/clients/edit/{id}', 'ClientController@edit')->name('clients.edit');
	Route::delete('/clients/delete/{id}', 'ClientController@delete')->name('clients.delete');
	Route::get('/clients/create', 'ClientController@create')->name('clients.create');
	Route::post('/clients/store', 'ClientController@store')->name('clients.store');
	Route::put('/clients/update', 'ClientController@update')->name('clients.update');
	Route::get('/clients/success', 'ClientController@success')->name('clients.success');

	Route::get('/clients/{id}/client_employees', 'ClientEmployeeController@index')->name('client_employees.index');
	Route::get('/clients/{id}/client_employees/admin/create', 'ClientEmployeeController@createAdmin')->name('client_employees.create.admin');
	Route::post('/clients/{id}/client_employees/admin/store', 'ClientEmployeeController@storeAdmin')->name('client_employees.store.admin');
	Route::put('/clients/{id}/client_employees/{client_employee_id}/restore', 'ClientEmployeeController@restore')->name('client_employees.restore');

	Route::get('/clients/{id}/client_users', 'ClientUserController@index')->name('client_users.index');
	Route::get('/clients/{id}/client_users/admin/create', 'ClientUserController@createAdmin')->name('client_users.create.admin');
	Route::post('/clients/{id}/client_users/admin/store', 'ClientUserController@storeAdmin')->name('client_users.store.admin');
	Route::put('/clients/{id}/client_users/{client_user_id}/restore', 'ClientUserController@restore')->name('client_users.restore');

	// 支店
	Route::get('/client_branches', 'ClientBranchesController@index')->name('client_branches.index');
	Route::get('/client_branches/create', 'ClientBranchesController@create')->name('client_branches.create');
	Route::post('/client_branches/store', 'ClientBranchesController@store')->name('client_branches.store');
	Route::get('/client_branches/edit/{id}', 'ClientBranchesController@edit')->name('client_branches.edit');
	Route::put('/client_branches/update/{id}', 'ClientBranchesController@update')->name('client_branches.update');
	Route::delete('/client_branches/delete/{id}', 'ClientBranchesController@delete')->name('client_branches.delete');
	// TRCD端末
	Route::get('/trcd_terminals', 'TrcdTerminalsController@index')->name('trcd_terminals.index');
	Route::get('/trcd_terminals/create', 'TrcdTerminalsController@create')->name('trcd_terminals.create');
	Route::post('/trcd_terminals/store', 'TrcdTerminalsController@store')->name('trcd_terminals.store');
	Route::get('/trcd_terminals/edit/{id}', 'TrcdTerminalsController@edit')->name('trcd_terminals.edit');
	Route::put('/trcd_terminals/update/{id}', 'TrcdTerminalsController@update')->name('trcd_terminals.update');
	Route::delete('/trcd_terminals/delete/{id}', 'TrcdTerminalsController@delete')->name('trcd_terminals.delete');
	// TRCD端末実行コマンド
	Route::get('/trcd_terminals/{id}/commands', 'TrcdTerminalCommandsController@index')->name('trcd_terminal_commands.index');
	Route::get('/trcd_terminals/{id}/commands/create', 'TrcdTerminalCommandsController@create')->name('trcd_terminal_commands.create');
	Route::post('/trcd_terminals/{id}/commands/store', 'TrcdTerminalCommandsController@store')->name('trcd_terminal_commands.store');
	Route::delete('/trcd_terminal_commands/delete/{id}', 'TrcdTerminalCommandsController@delete')->name('trcd_terminal_commands.delete');

	// cacheクリア用
	Route::get('system/cache/clear', 'SystemController@clear_cache')->name('system.cache.clear');
	Route::delete('system/cache', 'SystemController@execute_clear_cache')->name('system.cache.clear.execute');
	//Route::get('artisan/cache/clear', 'ArtisanController@clear_cache')->name('cache.clear');

	// 引出し済み金額更新
	Route::put('/clients/{id}/update/withdraw_amount_already_this_month', 'ClientController@update_withdraw_amount_already_this_month')->name('clients.update.withdraw_amount_already_this_month');
});

// ==================== Trcd管理画面 ====================
Route::group(['prefix' => 'trcd'], function() {
	// TRCD管理画面 ログイン
	Route::get('login/{auth_key?}', 'Trcd\LoginController@showLoginForm')->name('trcd.login');
	Route::post('login', 'Trcd\LoginController@login');
	// @YuKaneko 2019/10/21 clients.auth_keyによる勤怠CSV出力
	Route::get('download/attendances', 'Trcd\AggregatesController@download_attendance_csv_by_auth_key')->name('trcd.aggregates.attendance.download_by_auth_key');
	// @YuKaneko 2020/02/21 clients.auth_keyによる払出し集計CSV出力
	Route::get('download/withdrawals', 'Trcd\AggregatesController@download_withdrawal_csv_by_auth_key')->name('trcd.aggregates.withdrawal.download_by_auth_key');
});

Route::group(['middleware' => 'auth:trcd', 'prefix' => 'trcd'], function() {
	// TRCD管理画面 ホーム
	Route::get('/', 'Trcd\HomeController@index')->name('trcd.home');
	//Route::get('home', 'Trcd\HomeController@index')->name('trcd.home');
	//Route::get('/', function() { return redirect(route('trcd.home')); });
	// TRCD管理画面 ログアウト
	Route::post('logout',		'Trcd\LoginController@logout')->name('trcd.logout');

	// ==================== 社員 ====================
	// 社員 一覧
	Route::get('client_employees', 'Trcd\ClientEmployeesController@index')->name('trcd.client_employees.index')->middleware('permission:read client_employees');
	// 社員 登録
	Route::group(['middleware' => ['permission:create client_employees']], function () {
		Route::get('client_employees/create', 'Trcd\ClientEmployeesController@create')->name('trcd.client_employees.create');
		Route::post('client_employees/store', 'Trcd\ClientEmployeesController@store')->name('trcd.client_employees.store');
	});
	// 社員 編集
	Route::group(['middleware' => ['permission:update client_employees']], function () {
		Route::get('client_employees/edit/{id}', 'Trcd\ClientEmployeesController@edit')->name('trcd.client_employees.edit');
		Route::put('client_employees/update/{id}', 'Trcd\ClientEmployeesController@update')->name('trcd.client_employees.update');
	});
	// パスワード変更
	Route::group(['middleware' => ['permission:update client_employees|reset password']], function () {
		Route::get('password/reset', 'Trcd\ClientEmployeesController@password_reset_index')->name('trcd.client_employees.password_reset_index');
		Route::get('client_employees/{id}/password_reset', 'Trcd\ClientEmployeesController@password_reset')->name('trcd.client_employees.password_reset');
		Route::put('client_employees/{id}/password_reset', 'Trcd\ClientEmployeesController@password_reset')->name('trcd.client_employees.password_reset');
	});
	// 社員 削除
	Route::delete('client_employees/delete/{id}', 'Trcd\ClientEmployeesController@delete')->name('trcd.client_employees.delete')->middleware('permission:delete client_employees');
  //社員インポート・エクスポート
	Route::get('client_employee_imports/import', 'Trcd\ClientEmployeeImportsController@import')->name('trcd.client_employee_imports.import')->middleware('permission:import client_employees');
	Route::get('client_employee_imports/upload', 'Trcd\ClientEmployeeImportsController@upload')->name('trcd.client_employee_imports.upload')->middleware('permission:import client_employees');
	Route::post('client_employee_imports/upload', 'Trcd\ClientEmployeeImportsController@upload')->name('trcd.client_employee_imports.upload')->middleware('permission:import client_employees');
	Route::get('client_employee_imports/export', 'Trcd\ClientEmployeeImportsController@export')->name('trcd.client_employee_imports.export')->middleware('permission:export client_employees');
	Route::post('client_employee_imports/export', 'Trcd\ClientEmployeeImportsController@export')->name('trcd.client_employee_imports.export')->middleware('permission:export client_employees');
	// ==================== 勤怠 ====================
	// 勤怠申請 一覧
	Route::get('attendances/requests', 'Trcd\AttendanceRequestsController@index')->name('trcd.attendances.requests.index')->middleware('permission:read attendances');
	// 勤怠申請フォーム
	Route::get('attendances/requests/form', 'Trcd\AttendanceRequestsController@fetch_form')->name('trcd.attendances.requests.form')->middleware('permission:request attendances|approve attendances|deny attendances');
	// 勤怠申請 申請
	Route::post('attendances/requests', 'Trcd\AttendanceRequestsController@request')->name('trcd.attendances.request')->middleware('permission:request attendances');
	// 勤怠申請 取消
	Route::delete('attendances/requests/{request_id}/cancel', 'Trcd\AttendanceRequestsController@cancel')->name('trcd.attendances.requests.cancel')->middleware('permission:request attendances');
	// 勤怠申請 承認
	Route::post('attendances/requests/{request_id}/approve', 'Trcd\AttendanceRequestsController@approve')->name('trcd.attendances.requests.approve')->middleware('permission:approve attendances');
	Route::post('attendances/requests/{request_id}/deny', 'Trcd\AttendanceRequestsController@deny')->name('trcd.attendances.requests.deny')->middleware('permission:deny attendances');

	// 勤怠 一覧
	Route::get('attendances', 'Trcd\AttendancesController@index')->name('trcd.attendances.index')->middleware('permission:read attendances');
	Route::get('client_employees/{client_employee_id}/attendances', 'Trcd\AttendancesController@view')->name('trcd.attendances.view')->middleware('permission:read attendances');

	// 勤怠ヘッダ 編集
	Route::get('client_employees/{client_employee_id}/attendances/{attendance_header_id}', 'Trcd\AttendancesController@edit')->name('trcd.attendances.edit')->middleware('permission:update attendances');
	Route::put('client_employees/{client_employee_id}/attendances/{attendance_header_id}', 'Trcd\AttendancesController@update')->name('trcd.attendances.update')->middleware('permission:update attendances');
	// 勤怠ヘッダ 削除
	Route::delete('client_employees/{client_employee_id}/attendances/delete/{attendance_header_id}', 'Trcd\AttendancesController@delete')->name('trcd.attendances.delete')->middleware('permission:delete attendances');

	// 勤怠詳細 編集
	Route::get('client_employees/{client_employee_id}/attendances/{attendance_header_id}/details/{attendance_detail_id?}', 'Trcd\AttendancesController@edit_attendance_detail')->name('trcd.attendances.edit.attendance_detail')->middleware('permission:update attendances');
	Route::post('client_employees/{client_employee_id}/attendances/{attendance_header_id}/details', 'Trcd\AttendancesController@store_attendance_detail')->name('trcd.attendances.store.attendance_detail')->middleware('permission:create attendances');
	Route::put('client_employees/{client_employee_id}/attendances/{attendance_header_id}/details/{attendance_detail_id}', 'Trcd\AttendancesController@update_attendance_detail')->name('trcd.attendances.update.attendance_detail')->middleware('permission:update attendances');
	// 勤怠詳細 削除
	Route::delete('client_employees/{client_employee_id}/attendances/{attendance_header_id}/details/delete/{attendance_detail_id}', 'Trcd\AttendancesController@delete_attendance_detail')->name('trcd.attendances.delete.attendance_detail')->middleware('permission:delete attendances');

	// 有給ヘッダ 編集
	Route::get('client_employees/{client_employee_id}/attendance_paid_holidays/{attendance_paid_holiday_id}', 'Trcd\AttendancesController@edit_attendance_paid_holiday')->name('trcd.attendances.edit.attendance_paid_holiday')->middleware('permission:update attendances');
	Route::put('client_employees/{client_employee_id}/attendance_paid_holidays/{attendance_paid_holiday_id}', 'Trcd\AttendancesController@update_attendance_paid_holiday')->name('trcd.attendances.update.attendance_paid_holiday')->middleware('permission:update attendances');
	// 有給ヘッダ 削除
	Route::delete('client_employees/{client_employee_id}/attendance_paid_holidays/delete/{attendance_paid_holiday_id}', 'Trcd\AttendancesController@delete_attendance_paid_holiday')->name('trcd.attendances.delete.attendance_paid_holiday')->middleware('permission:delete attendances');

	// 簡易打刻
	Route::get('attendances/simplified', 'Trcd\AttendancesController@simple_stamp')->name('trcd.attendances.simplified')->middleware('permission:create attendances');
	// 簡易打刻 登録処理
	Route::post('attendances/simplified', 'Trcd\AttendancesController@store_simple_stamp')->name('trcd.attendances.simplified.store')->middleware('permission:create attendances');

	// 日付増加
	Route::put('client_employees/{client_employee_id}/attendances/{attendance_header_id}/increase_date', 'Trcd\AttendancesController@increase_date_by_one_day')->name('trcd.attendances.increasedate')->middleware('permission:update attendances');
	// 日付減少
	Route::put('client_employees/{client_employee_id}/attendances/{attendance_header_id}/decrease_date', 'Trcd\AttendancesController@decrease_date_by_one_day')->name('trcd.attendances.decreasedate')->middleware('permission:update attendances');

  //勤怠情報再計算
  Route::get('attendances/recalculate','Trcd\AttendancesController@recalculate')->name('trcd.attendances.recalculate')->middleware('permission:update attendances');
  Route::post('attendances/recalculate','Trcd\AttendancesController@recalculate')->name('trcd.attendances.recalculate.exec')->middleware('permission:update attendances');

  	// 休日 登録
 	Route::post('client_employees/{client_employee_id}/attendance_notes', 'Trcd\AttendancesController@store_attendance_note')->name('trcd.attendance_notes.store')->middleware('permission:create holidays');
  	// 休日 削除
	Route::delete('client_employees/{client_employee_id}/attendance_notes/{attendance_note_id}', 'Trcd\AttendancesController@delete_attendance_note')->name('trcd.attendance_notes.delete')->middleware('permission:delete holidays');

	// 勤怠 日毎一覧
	Route::get('attendances/daily', 'Trcd\AttendancesController@daily')->name('trcd.attendances.daily')->middleware('permission:access daily_attendance_page');

	// ==================== 入出金 ====================
	//入出金管理
	Route::get('payments', 'Trcd\PaymentsController@index')->name('trcd.payments.index')->middleware('permission:read payment_histories');
	// 勤怠所属グループ
	Route::get('client_groups', 'Trcd\ClientGroupsController@index')->name('trcd.client_groups.index')->middleware('permission:read client_groups');
	Route::get('client_groups/create', 'Trcd\ClientGroupsController@create')->name('trcd.client_groups.create')->middleware('permission:create client_groups');
	Route::post('client_groups/store', 'Trcd\ClientGroupsController@store')->name('trcd.client_groups.store')->middleware('permission:create client_groups');
	Route::get('client_groups/edit/{id}', 'Trcd\ClientGroupsController@edit')->name('trcd.client_groups.edit')->middleware('permission:update client_groups');
	Route::put('client_groups/update/{id}', 'Trcd\ClientGroupsController@update')->name('trcd.client_groups.update')->middleware('permission:update client_groups');
	Route::delete('client_groups/delete/{id}', 'Trcd\ClientGroupsController@delete')->name('trcd.client_groups.delete')->middleware('permission:delete client_groups');
	//休憩
  Route::get('break_types', 'Trcd\BreakTypesController@index')->name('trcd.break_types.index')->middleware('permission:read break_types');
  Route::get('break_types/create', 'Trcd\BreakTypesController@create')->name('trcd.break_types.create')->middleware('permission:create break_types');
	Route::post('break_types/store', 'Trcd\BreakTypesController@store')->name('trcd.break_types.store')->middleware('permission:create break_types');
	Route::get('break_types/edit/{id}', 'Trcd\BreakTypesController@edit')->name('trcd.break_types.edit')->middleware('permission:update break_types');
	Route::put('break_types/update/{id}', 'Trcd\BreakTypesController@update')->name('trcd.break_types.update')->middleware('permission:update break_types');
	Route::delete('break_types/delete/{id}', 'Trcd\BreakTypesController@delete')->name('trcd.break_types.delete')->middleware('permission:delete break_types');
	//出勤
  Route::get('attendance_patterns', 'Trcd\AttendancePatternsController@index')->name('trcd.attendance_patterns.index')->middleware('permission:read attendance_patterns');
  Route::get('attendance_patterns/create', 'Trcd\AttendancePatternsController@create')->name('trcd.attendance_patterns.create')->middleware('permission:create attendance_patterns');
	Route::post('attendance_patterns/store', 'Trcd\AttendancePatternsController@store')->name('trcd.attendance_patterns.store')->middleware('permission:create attendance_patterns');
	Route::get('attendance_patterns/edit/{id}', 'Trcd\AttendancePatternsController@edit')->name('trcd.attendance_patterns.edit')->middleware('permission:update attendance_patterns');
	Route::put('attendance_patterns/update/{id}', 'Trcd\AttendancePatternsController@update')->name('trcd.attendance_patterns.update')->middleware('permission:update attendance_patterns');
	Route::delete('attendance_patterns/delete/{id}', 'Trcd\AttendancePatternsController@delete')->name('trcd.attendance_patterns.delete')->middleware('permission:delete attendance_patterns');
	//特殊勤怠
	Route::get('attendance_special_types', 'Trcd\AttendanceSpecialTypesController@index')->name('trcd.attendance_special_types.index')->middleware('permission:read attendance_special_types');
	Route::get('attendance_special_types/create', 'Trcd\AttendanceSpecialTypesController@create')->name('trcd.attendance_special_types.create')->middleware('permission:create attendance_special_types');
	Route::post('attendance_special_types/store', 'Trcd\AttendanceSpecialTypesController@store')->name('trcd.attendance_special_types.store')->middleware('permission:create attendance_special_types');
	Route::get('attendance_special_types/edit/{id}', 'Trcd\AttendanceSpecialTypesController@edit')->name('trcd.attendance_special_types.edit')->middleware('permission:update attendance_special_types');
	Route::put('attendance_special_types/update/{id}', 'Trcd\AttendanceSpecialTypesController@update')->name('trcd.attendance_special_types.update')->middleware('permission:update attendance_special_types');
	Route::delete('attendance_special_types/delete/{id}', 'Trcd\AttendanceSpecialTypesController@delete')->name('trcd.attendance_special_types.delete')->middleware('permission:delete attendance_special_types');
	//勤怠集計エクスポート設定
	//Route::get('attendance_exports/setting', 'Trcd\AttendanceExportsController@setting')->name('trcd.attendance_exports.setting');
	//Route::get('attendance_exports/export', 'Trcd\AttendanceExportsController@export')->name('trcd.attendance_exports.export');
	//勤怠集計エクスポート設定Ver2
	Route::group(['middleware' => ['permission:export attendance_aggregations']], function () {
		Route::get('attendance_exports/setting', 'Trcd\AttendanceExportsController@index')->name('trcd.attendance_exports.setting.index');
		Route::get('attendance_exports/create', 'Trcd\AttendanceExportsController@create')->name('trcd.attendance_exports.setting.create');
		Route::get('attendance_exports/form/{csv_export_type_id}', 'Trcd\AttendanceExportsController@fetch_form')->name('trcd.attendance_exports.setting.fetch_form');
		Route::post('attendance_exports/store', 'Trcd\AttendanceExportsController@store')->name('trcd.attendance_exports.setting.store');
		Route::get('attendance_exports/edit/{id}', 'Trcd\AttendanceExportsController@edit')->name('trcd.attendance_exports.setting.edit');
		Route::put('attendance_exports/update/{id}', 'Trcd\AttendanceExportsController@update')->name('trcd.attendance_exports.setting.update');
		Route::delete('attendance_exports/delete/{id}', 'Trcd\AttendanceExportsController@delete')->name('trcd.attendance_exports.setting.delete');
	});
	//有給
	Route::get('paid_holidays', 'Trcd\PaidHolidaysController@index')->name('trcd.paid_holidays.index')->middleware('permission:read paid_holidays');
	Route::get('paid_holidays/create', 'Trcd\PaidHolidaysController@create')->name('trcd.paid_holidays.create')->middleware('permission:create paid_holidays');
	Route::post('paid_holidays/store', 'Trcd\PaidHolidaysController@store')->name('trcd.paid_holidays.store')->middleware('permission:create paid_holidays');
	Route::get('paid_holidays/edit/{id}', 'Trcd\PaidHolidaysController@edit')->name('trcd.paid_holidays.edit')->middleware('permission:update paid_holidays');
	Route::put('paid_holidays/update/{id}', 'Trcd\PaidHolidaysController@update')->name('trcd.paid_holidays.update')->middleware('permission:update paid_holidays');
	Route::delete('paid_holidays/delete/{id}', 'Trcd\PaidHolidaysController@delete')->name('trcd.paid_holidays.delete')->middleware('permission:delete paid_holidays');
	//各種設定
	Route::group(['middleware' => ['permission:update payroll_start_day|update withdraw_amount_limit_a_day|update rounding_franction']], function () {
		Route::get('settings', 'Trcd\ClientTrcdSettingsController@index')->name('trcd.client_settings.index');
		Route::put('settings', 'Trcd\ClientTrcdSettingsController@update')->name('trcd.client_settings.update');
	});
	//TRCDエラーログ
	Route::group(['middleware' => ['permission:read trcd_messages']], function () {
		Route::get('trcd_messages', 'Trcd\TrcdMessagesController@index');
		Route::get('trcd_messages/daily', 'Trcd\TrcdMessagesController@daily');
		Route::get('trcd_messages/ajax/get_daily_message/{alert_date}', 'Trcd\TrcdMessagesController@ajax_get_daily_message');
	});
  //メッセージ
  Route::get('messages', 'Trcd\MessagesController@index')->name('trcd.messages.index')->middleware('permission:read messages');
  Route::get('messages/create', 'Trcd\MessagesController@create')->name('trcd.messages.create')->middleware('permission:create messages');
  Route::post('messages/store', 'Trcd\MessagesController@store')->name('trcd.messages.store')->middleware('permission:create messages');
  Route::get('messages/edit/{id}', 'Trcd\MessagesController@edit')->name('trcd.messages.edit')->middleware('permission:update messages');
  Route::put('messages/update/{id}', 'Trcd\MessagesController@update')->name('trcd.messages.update')->middleware('permission:update messages');
  Route::delete('messages/delete/{id}', 'Trcd\MessagesController@delete')->name('trcd.messages.delete')->middleware('permission:delete messages');
	//集計 払出し
	Route::get('aggregates/withdrawal', 'Trcd\AggregatesController@aggregate_withdrawal')->name('trcd.aggregates.withdrawal')->middleware('permission:read withdrawal_aggregations');
	Route::get('aggregates/withdrawal/{year}/{month}', 'Trcd\AggregatesController@download_withdrawal_csv')->name('trcd.aggregates.withdrawal.download')->middleware('permission:export withdrawal_aggregations');
	//集計 勤怠
	Route::get('aggregates/attendance', 'Trcd\AggregatesController@aggregate_attendance')->name('trcd.aggregates.attendance')->middleware('permission:read attendance_aggregations');
	Route::get('aggregates/attendance/{year}/{month}', 'Trcd\AggregatesController@download_attendance_csv')->name('trcd.aggregates.attendance.download')->middleware('permission:export attendance_aggregations');
	// ヘルプ マニュアル
	Route::get('manuals/latest/download', 'Trcd\ManualsController@download_latest_version')->name('trcd.manuals.downlod.latest');

	// 経費精算・仮払い系 --------------------------------------------------
	// 経費精算 経費概要検索
	Route::get('expense_summaries', 'Trcd\ExpenseSummariesController@index')->name('trcd.expense_summaries.index')->middleware('permission:read expenses');

	// 経費精算 経費概要登録
	Route::get('expense_summaries/create', 'Trcd\ExpenseSummariesController@create')->name('trcd.expense_summaries.create')->middleware('permission:create expenses');
	Route::post('expense_summaries/store', 'Trcd\ExpenseSummariesController@store')->name('trcd.expense_summaries.store')->middleware('permission:create expenses');
	Route::get('expense_summaries/v2/create', 'Trcd\ExpenseSummariesController@create')->name('trcd.expense_summaries.v2.create')->middleware('permission:create expenses');
	Route::post('expense_summaries/v2/store', 'Trcd\ExpenseSummariesController@store')->name('trcd.expense_summaries.v2.store')->middleware('permission:create expenses');

	// 経費精算 経費概要詳細
	Route::get('expense_summaries/{id}/view', 'Trcd\ExpenseSummariesController@view')->name('trcd.expense_summaries.view')->middleware('permission:read expenses');
	Route::get('expense_summaries/{id}/v2/view', 'Trcd\ExpenseSummariesController@view')->name('trcd.expense_summaries.v2.view')->middleware('permission:read expenses');

	// 経費精算 経費概要下書き編集
	Route::get('expense_summaries/{id}', 'Trcd\ExpenseSummariesController@edit')->name('trcd.expense_summaries.edit')->middleware('permission:update expenses');
	Route::put('expense_summaries/{id}', 'Trcd\ExpenseSummariesController@update')->name('trcd.expense_summaries.update')->middleware('permission:update expenses');
	Route::get('expense_summaries/v2/{id}', 'Trcd\ExpenseSummariesController@edit')->name('trcd.expense_summaries.v2.edit')->middleware('permission:update expenses');
	Route::put('expense_summaries/v2/{id}', 'Trcd\ExpenseSummariesController@update')->name('trcd.expense_summaries.v2.update')->middleware('permission:update expenses');

	// 経費精算 フォーム取得
	Route::get('expense_summaries/form/{index}', 'Trcd\ExpenseSummariesController@fetch_slide_form')->name('trcd.expense_summaries.form');
	Route::get('expense_summaries/form/v2/{index}', 'Trcd\ExpenseSummariesController@fetch_table_form_row')->name('trcd.expense_summaries.form.v2');

	// 経費精算 経費用画像データを取得
	Route::get('client_employees/{client_employee_id}/expense_images', 'Trcd\ExpenseSummariesController@select_expense_images')->name('trcd.expense_summaries.select_expense_images')->middleware('permission:read expenses');

	// 経費精算 経費用画像を登録
        Route::post('expense_images', 'Trcd\ExpenseSummariesController@store_expense_image')->name('trcd.expense_summaries.store_expense_image')->middleware('permission:create expenses');

	// 経費精算 経費用画像を削除
        Route::delete('expense_images/{id}', 'Trcd\ExpenseSummariesController@delete_expense_image')->name('trcd.expense_summaries.delete_expense_image')->middleware('permission:create expenses');

	// 経費精算 下書き削除
	Route::delete('expense_summaries/{id}/delete_draft', 'Trcd\ExpenseSummariesController@delete_draft')->name('trcd.expense_summaries.delete_draft')->middleware('permission:delete expenses');

	// 経費精算 下書きに戻す
	Route::put('expense_summaries/{id}/revert_to_draft', 'Trcd\ExpenseSummariesController@revert_to_draft')->name('trcd.expense_summaries.revert_to_draft')->middleware('permission:approve expenses');

	// 経費精算 承認
	Route::put('expense_summaries/{id}/approve', 'Trcd\ExpenseSummariesController@approve')->name('trcd.expense_summaries.approve')->middleware('permission:approve expenses');

	// 経費精算 否認
	Route::delete('expense_summaries/{id}/reject', 'Trcd\ExpenseSummariesController@reject')->name('trcd.expense_summaries.reject')->middleware('permission:approve expenses');

	// 経費概要 ロック解除
	Route::put('expense_summaries/{id}/unlock', 'Trcd\ExpenseSummariesController@unlock')->name('trcd.expense_summaries.unlock')->middleware('permission:unlock expenses');

	// 経費ヘッダ レシート現物受取り済フラグ更新
	Route::put('expense_headers/{id}/update_has_original_receipt_been_received', 'Trcd\ExpenseHeadersController@update_has_original_receipt_been_received')->name('trcd.expense_headers.update_has_original_receipt_been_received')->where('id', '[0-9]+')->middleware('permission:update expenses');

	// 経費集計エクスポート設定
	Route::group(['middleware' => ['permission:export expense_aggregations']], function () {
		Route::get('expense_exports/setting', 'Trcd\ExpenseExportsController@index')->name('trcd.expense_exports.setting.index');
		Route::get('expense_exports/create', 'Trcd\ExpenseExportsController@create')->name('trcd.expense_exports.setting.create');
		Route::post('expense_exports/create', 'Trcd\ExpenseExportsController@store')->name('trcd.expense_exports.setting.store');
		Route::get('expense_exports/edit/{id}', 'Trcd\ExpenseExportsController@edit')->name('trcd.expense_exports.setting.edit');
		Route::put('expense_exports/edit/{id}', 'Trcd\ExpenseExportsController@update')->name('trcd.expense_exports.setting.update');
		Route::delete('expense_exports/delete/{id}', 'Trcd\ExpenseExportsController@delete')->name('trcd.expense_exports.setting.delete');
	});
	// 経費集計エクスポート
	Route::group(['middleware' => ['permission:read expense_aggregations']], function () {
		Route::get('aggregates/expense', 'Trcd\Aggregate\ExpenseAggregatesController@index')->name('trcd.aggregates.expense');
		Route::get('aggregates/expense/download', 'Trcd\Aggregate\ExpenseAggregatesController@download')->name('trcd.aggregates.expense.download');
	});

	// 勘定科目 設定
	Route::get('account_title_clients', 'Trcd\AccountTitleClientsController@index')->name('trcd.account_title_clients.index')->middleware('permission:update account_titles');
	Route::put('account_title_clients', 'Trcd\AccountTitleClientsController@update')->name('trcd.account_title_clients.update')->middleware('permission:update account_titles');

	// 勘定科目 登録
	Route::post('account_title', 'Trcd\AccountTitlesController@store')->name('trcd.account_titles.store')->middleware('permission:update account_titles');
	// 勘定科目 編集
	Route::get('account_title/{id}', 'Trcd\AccountTitlesController@edit')->name('trcd.account_titles.edit')->middleware('permission:update account_titles');
	Route::put('account_title/{id}', 'Trcd\AccountTitlesController@update')->name('trcd.account_titles.update')->middleware('permission:update account_titles');
	// 勘定科目 削除
	Route::delete('account_title/{id}', 'Trcd\AccountTitlesController@delete')->name('trcd.account_titles.delete')->middleware('permission:update account_titles');

	// 仮払い 仮払い概要検索
	Route::get('temporary_payments', 'Trcd\TemporaryPaymentsController@index')->name('trcd.temporary_payments.index')->middleware('permission:read temporary_payments');

	// 仮払い 仮払い概要登録
        Route::get('temporary_payments/create', 'Trcd\TemporaryPaymentsController@create')->name('trcd.temporary_payments.create')->middleware('permission:create temporary_payments');
	Route::post('temporary_payments/store', 'Trcd\TemporaryPaymentsController@store')->name('trcd.temporary_payments.store')->middleware('permission:create temporary_payments');

	// 仮払い 仮払い概要詳細
	Route::get('temporary_payments/{id}/view', 'Trcd\TemporaryPaymentsController@view')->name('trcd.temporary_payments.view')->middleware('permission:read temporary_payments|read expenses');
        Route::get('temporary_payments/{id}/v2/view', 'Trcd\TemporaryPaymentsController@view')->name('trcd.temporary_payments.v2.view')->middleware('permission:read temporary_payments|read expenses');

	// 仮払い 合計金額登録
	Route::get('temporary_payments/{id}/edit_total_amount', 'Trcd\TemporaryPaymentsController@edit_total_amount')->name('trcd.temporary_payments.edit_total_amount')->middleware('permission:create expenses');
        Route::put('temporary_payments/{id}/update_total_amount', 'Trcd\TemporaryPaymentsController@update_total_amount')->name('trcd.temporary_payments.update_total_amount')->middleware('permission:create expenses');
        Route::get('temporary_payments/v2/{id}/edit_total_amount', 'Trcd\TemporaryPaymentsController@edit_total_amount')->name('trcd.temporary_payments.v2.edit_total_amount')->middleware('permission:create expenses');
	Route::put('temporary_payments/v2/{id}/update_total_amount', 'Trcd\TemporaryPaymentsController@update_total_amount')->name('trcd.temporary_payments.v2.update_total_amount')->middleware('permission:create expenses');

	// 仮払い 下書き編集
	Route::get('temporary_payments/{id}/edit_draft', 'Trcd\TemporaryPaymentsController@edit_draft')->name('trcd.temporary_payments.edit_draft')->middleware('permission:update expenses');
        Route::put('temporary_payments/{id}/update_draft', 'Trcd\TemporaryPaymentsController@update_draft')->name('trcd.temporary_payments.update_draft')->middleware('permission:update expenses');
        Route::get('temporary_payments/v2/{id}/edit_draft', 'Trcd\TemporaryPaymentsController@edit_draft')->name('trcd.temporary_payments.v2.edit_draft')->middleware('permission:update expenses');
        Route::put('temporary_payments/v2/{id}/update_draft', 'Trcd\TemporaryPaymentsController@update_draft')->name('trcd.temporary_payments.v2.update_draft')->middleware('permission:update expenses');

	// 仮払い 削除
        Route::delete('temporary_payments/{id}/delete', 'Trcd\TemporaryPaymentsController@delete')->name('trcd.temporary_payments.delete')->middleware('permission:delete temporary_payments');
	// 仮払い 下書き削除
        Route::delete('temporary_payments/{id}/delete_draft', 'Trcd\TemporaryPaymentsController@delete_draft')->name('trcd.temporary_payments.delete_draft')->middleware('permission:delete expenses');
	// 仮払い 下書きに戻す
        Route::put('temporary_payments/{id}/revert_to_draft', 'Trcd\TemporaryPaymentsController@revert_to_draft')->name('trcd.temporary_payments.revert_to_draft')->middleware('permission:approve expenses');

	// 仮払い 承認
        Route::put('temporary_payments/{id}/approve', 'Trcd\TemporaryPaymentsController@approve')->name('trcd.temporary_payments.approve')->middleware('permission:approve expenses');

        // 仮払い 否認
        Route::delete('temporary_payments/{id}/reject', 'Trcd\TemporaryPaymentsController@reject')->name('trcd.temporary_payments.reject')->middleware('permission:approve expenses');

        // 仮払い ロック解除
        Route::put('temporary_payments/{id}/unlock_before_pay_out', 'Trcd\TemporaryPaymentsController@unlock_before_pay_out')->name('trcd.temporary_payments.unlock_before_pay_out')->middleware('permission:unlock temporary_payments');
        Route::put('temporary_payments/{id}/unlock_after_pay_out', 'Trcd\TemporaryPaymentsController@unlock_after_pay_out')->name('trcd.temporary_payments.unlock_after_pay_out')->middleware('permission:unlock expenses');

	// Nextcloudとの連携を設定する
	Route::post('client_employees/{client_employee_id}/configure_cooperation_with_nextcloud', 'Trcd\ExpenseSummariesController@configure_cooperation_with_nextcloud')->where('client_employee_id', '[0-9]+')->name('trcd.expense_summaries.configure_cooperation_with_nextcloud')->middleware('permission:create expenses');
	// Nextcloudの情報を表示する
	Route::get('client_employees/{client_employee_id}/display_nextcloud_information', 'Trcd\ExpenseSummariesController@display_nextcloud_information')->where('client_employee_id', '[0-9]+')->name('trcd.expense_summaries.display_nextcloud_information')->middleware('permission:create expenses');
    // 仮払い集計エクスポート設定
    Route::group(['middleware' => ['permission:export temporary_payment_aggregations']], function () {
        Route::get('temporary_payment_exports/setting', 'Trcd\TemporaryPaymentExportsController@index')->name('trcd.temporary_payment_exports.setting.index');
        Route::get('temporary_payment_exports/create', 'Trcd\TemporaryPaymentExportsController@create')->name('trcd.temporary_payment_exports.setting.create');
        Route::post('temporary_payment_exports/create', 'Trcd\TemporaryPaymentExportsController@store')->name('trcd.temporary_payment_exports.setting.store');
        Route::get('temporary_payment_exports/edit/{id}', 'Trcd\TemporaryPaymentExportsController@edit')->name('trcd.temporary_payment_exports.setting.edit');
        Route::put('temporary_payment_exports/edit/{id}', 'Trcd\TemporaryPaymentExportsController@update')->name('trcd.temporary_payment_exports.setting.update');
        Route::delete('temporary_payment_exports/delete/{id}', 'Trcd\TemporaryPaymentExportsController@delete')->name('trcd.temporary_payment_exports.setting.delete');
    });

	// 仮払い集計エクスポート
	Route::group(['middleware' => ['permission:read temporary_payment_aggregations']], function () {
		Route::get('aggregates/temporary_payment', 'Trcd\Aggregate\TemporaryPaymentAggregatesController@index')->name('trcd.aggregates.temporary_payment');
		Route::get('aggregates/temporary_payment/download', 'Trcd\Aggregate\TemporaryPaymentAggregatesController@download')->name('trcd.aggregates.temporary_payment.download');
	});

	// 仮払い概要候補
	Route::get('temporary_payment_summary_candidates', 'Trcd\TemporaryPaymentSummaryCandidateController@index')->name('trcd.temporary_payment_summary_candidates.index')->middleware('permission:read temporary_payment_summary_candidates');
	Route::get('temporary_payment_summary_candidates/create', 'Trcd\TemporaryPaymentSummaryCandidateController@create')->name('trcd.temporary_payment_summary_candidates.create')->middleware('permission:create temporary_payment_summary_candidates');
	Route::post('temporary_payment_summary_candidates/store', 'Trcd\TemporaryPaymentSummaryCandidateController@store')->name('trcd.temporary_payment_summary_candidates.store')->middleware('permission:create temporary_payment_summary_candidates');
	Route::get('temporary_payment_summary_candidates/edit/{id}', 'Trcd\TemporaryPaymentSummaryCandidateController@edit')->name('trcd.temporary_payment_summary_candidates.edit')->middleware('permission:update temporary_payment_summary_candidates');
	Route::put('temporary_payment_summary_candidates/update/{id}', 'Trcd\TemporaryPaymentSummaryCandidateController@update')->name('trcd.temporary_payment_summary_candidates.update')->middleware('permission:update temporary_payment_summary_candidates');
	Route::delete('temporary_payment_summary_candidates/delete/{id}', 'Trcd\TemporaryPaymentSummaryCandidateController@delete')->name('trcd.temporary_payment_summary_candidates.delete')->middleware('permission:delete temporary_payment_summary_candidates');

	// 経費所属グループ
	Route::get('expense_groups', 'Trcd\ExpenseGroupsController@index')->name('trcd.expense_groups.index')->middleware('permission:read expense_groups');
	Route::get('expense_groups/create', 'Trcd\ExpenseGroupsController@create')->name('trcd.expense_groups.create')->middleware('permission:create expense_groups');
	Route::post('expense_groups/store', 'Trcd\ExpenseGroupsController@store')->name('trcd.expense_groups.store')->middleware('permission:create expense_groups');
	Route::get('expense_groups/edit/{id}', 'Trcd\ExpenseGroupsController@edit')->name('trcd.expense_groups.edit')->middleware('permission:update expense_groups');
	Route::put('expense_groups/update/{id}', 'Trcd\ExpenseGroupsController@update')->name('trcd.expense_groups.update')->middleware('permission:update expense_groups');
	Route::delete('expense_groups/delete/{id}', 'Trcd\ExpenseGroupsController@delete')->name('trcd.expense_groups.delete')->middleware('permission:delete expense_groups');

	// 経費所属グループ設定
	Route::get('expense_group_settings', 'Trcd\ExpenseGroupSettingsController@index')->name('trcd.expense_group_settings.index')->middleware('permission:update expense_group_settings');
	Route::post('expense_group_settings', 'Trcd\ExpenseGroupSettingsController@update')->name('trcd.expense_group_settings.update')->middleware('permission:update expense_group_settings');

	// 残高不足通知設定
	Route::get('trcd_terminal_notification_settings', 'Trcd\TrcdTerminalNotificationSettingsController@index')->name('trcd.trcd_terminal_notification_settings.index')->middleware('permission:update trcd_terminal_notification_settings');
	Route::post('trcd_terminal_notification_settings', 'Trcd\TrcdTerminalNotificationSettingsController@update')->name('trcd.trcd_terminal_notification_settings.update')->middleware('permission:update trcd_terminal_notification_settings');

	// アルコールチェック履歴
	Route::get('trcd_alcohol_check_records', 'Trcd\TrcdAlcoholCheckRecordsController@index')->name('trcd.trcd_alcohol_check_records.index')->middleware('permission:read trcd_alcohol_check_records');
	Route::get('trcd_alcohol_check_records/download', 'Trcd\TrcdAlcoholCheckRecordsController@download')->name('trcd.trcd_alcohol_check_records.download')->middleware('permission:read trcd_alcohol_check_records');
});

// 生SQL文実行API
Route::get('queries/select', 'Api\SqlQueriesController@select')->middleware('ip');