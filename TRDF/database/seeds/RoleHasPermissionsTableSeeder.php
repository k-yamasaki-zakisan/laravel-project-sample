<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleHasPermissionsTableSeeder extends Seeder {
	private $__has_error = false; // エラー発生フラグ

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		DB::beginTransaction();

		try {
			//TRCD管理画面用
			$this->syncPermissionsToTrcdAdmin(); // 管理者
			$this->syncPermissionsToTrcdVeinInformationManager(); // 静脈情報管理者
			$this->syncPermissionsToTrcdAttendanceManager(); // 勤怠管理
			$this->syncPermissionsToTrcdAttendanceRequestManager(); // 勤怠申請管理
			$this->syncPermissionsToTrcdEmployeeManager(); // 社員管理
			$this->syncPermissionsToTrcdPaymentManager(); // 出入金管理
			$this->syncPermissionsToTrcdMessageManager(); // メッセージ管理
			$this->syncPermissionsToTrcdExpenseRegistration(); // 経費登録
			$this->syncPermissionsToTrcdExpenseApproval(); // 経費承認
			$this->syncPermissionsToTrcdExpenseManager(); // 経費管理
			$this->syncPermissionsToTrcdTemporaryPaymentManager(); // 仮払い管理
			$this->syncPermissionsToTrcdAttendanceRequestOnly(); // 勤怠申請のみ
			$this->syncPermissionsToTrcdAttendanceReadingOnly(); // 勤怠参照のみ

			if ( $this->__has_error ) throw new \Exception(__METHOD__ . " failed...");
		} catch (\Exception $e) {
			DB::rollBack();
			var_dump($e->getMessage());

			return;
		}

		DB::commit();
	}

	/*
		Role取得
	*/
	protected function findRole($role_name, $guard_name) {
		return Role::where('name', $role_name)
			->where('guard_name', $guard_name)
			->first();
	}

	/*
		Permission取得
	*/
	protected function findPermissions($permission_names, $guard_name) {
		return Permission::where('guard_name', $guard_name)
			->whereIn('name', $permission_names)
			->get();
	}

	/*
		TRCD管理者Roleに認可を設定
		@return void
	*/
	protected function syncPermissionsToTrcdAdmin() {
		$role = $this->findRole('ADMIN', 'trcd');
		
		if ( empty($role) ) return;

		$permission_names = config('database.trcd.permissions');
		$Permissions = $this->findPermissions($permission_names, 'trcd');

		if ( empty($role->syncPermissions($Permissions)) ) {
			logger()->error(__MEHTOD__ . " failed.");
			$this->__has_error = false;
		}
	}

	/*
		TRCD端末の静脈認証情報管理Roleに認可を設定
		@return void
	*/
	protected function syncPermissionsToTrcdVeinInformationManager() {
		$role = $this->findRole('VEIN_INFORMATION_MANAGER', 'trcd');
		
		if ( empty($role) ) return;

		$permission_names = [
			// TRCD端末の静脈認証
			'read trcd_vein_informations', // 閲覧
			'create trcd_vein_informations', //登録
			'update trcd_vein_informations', //編集
			'delete trcd_vein_informations', //削除
		];
		$Permissions = $this->findPermissions($permission_names, 'trcd');

		if ( empty($role->syncPermissions($Permissions)) ) {
			logger()->error(__MEHTOD__ . " failed.");
			$this->__has_error = false;
		}
	}

	/*
		TRCD勤怠管理Roleに認可を設定
		@return void
	*/
	protected function syncPermissionsToTrcdAttendanceManager() {
		$role = $this->findRole('ATTENDANCE_MANAGER', 'trcd');
		
		if ( empty($role) ) return;

		$permission_names = [
			// 社員
			'read client_employees', //閲覧
			// パスワード
			'reset password', // パスワード再設定
			// 勤怠
			'read attendances', //閲覧
			'create attendances', //登録
			'update attendances', //編集
			'delete attendances', //削除
			'request attendances', //申請
			'cancel attendances', //申請取消
			'approve attendances', //申請承認
			'deny attendances', //申請否認
			'read attendance_aggregations', //勤怠集計閲覧
			'export attendance_aggregations', //勤怠集計エクスポート
			// add YuKaneko @2019/11/25
			'request others_attendances', // 他者の勤怠申請
			'access daily_attendance_page', // 日毎一覧ページへアクセス
			'create holidays', // 休日登録
			'delete holidays', // 休日削除
			// 出入金
			// お知らせ
			'read messages', //閲覧
			'create messages', //登録
			'update messages', //編集
			'delete messages', //削除
			// trcd端末エラーログ
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
			// trcd企業設定
			// trcd端末への入出金
		];
		$Permissions = $this->findPermissions($permission_names, 'trcd');

		if ( empty($role->syncPermissions($Permissions)) ) {
			logger()->error(__MEHTOD__ . " failed.");
			$this->__has_error = false;
		}
	}

	/*
		TRCD勤怠申請管理Roleに認可を設定
		@return void
	*/
	protected function syncPermissionsToTrcdAttendanceRequestManager() {
		$role = $this->findRole('ATTENDANCE_REQUEST_MANAGER', 'trcd');
		
		if ( empty($role) ) return;

		$permission_names = [
			// パスワード
			'reset password', // パスワード再設定
			// 勤怠
			'read attendances', //閲覧
			'request attendances', //申請
			'cancel attendances', //申請取消
			// add YuKaneko @2019/11/25
			'request others_attendances', // 他者の勤怠申請
			'access daily_attendance_page', // 日毎一覧ページへアクセス
			'create holidays', // 休日登録
			'delete holidays', // 休日削除
		];
		$Permissions = $this->findPermissions($permission_names, 'trcd');

		if ( empty($role->syncPermissions($Permissions)) ) {
			logger()->error(__MEHTOD__ . " failed.");
			$this->__has_error = false;
		}
	}

	/*
		TRCD社員管理Roleに認可を設定
		@return void
	*/
	protected function syncPermissionsToTrcdEmployeeManager() {
		$role = $this->findRole('EMPLOYEE_MANAGER', 'trcd');
		
		if ( empty($role) ) return;

		$permission_names = [
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
			'request attendances', //申請
			'cancel attendances', //申請取消
			'read attendance_aggregations', //勤怠集計閲覧
			'export attendance_aggregations', //勤怠集計エクスポート
			// add YuKaneko @2019/11/25
			'access daily_attendance_page', // 日毎一覧ページへアクセス
			// 出入金
			// お知らせ
			'read messages', //閲覧
			'create messages', //登録
			'update messages', //編集
			'delete messages', //削除
			// trcd端末エラーログ
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
			// 特殊勤怠パターン
			// trcd企業設定
			// trcd端末への入出金
			'manage trcd_terminal', // trcd端末管理者
		];
		$Permissions = $this->findPermissions($permission_names, 'trcd');

		if ( empty($role->syncPermissions($Permissions)) ) {
			logger()->error(__MEHTOD__ . " failed.");
			$this->__has_error = false;
		}
	}

	/*
		TRCD出入金管理Roleに認可を設定
		@return void
	*/
	protected function syncPermissionsToTrcdPaymentManager() {
		$role = $this->findRole('PAYMENT_MANAGER', 'trcd');
		
		if ( empty($role) ) return;

		$permission_names = [
			// 社員
			'read client_employees', //閲覧
			'update client_employees', //編集
			// パスワード
			'reset password', // パスワード再設定
			// 勤怠
			'read attendances', //閲覧
			'request attendances', //申請
			'cancel attendances', //申請取消
			// 出入金
			'read payment_histories', //履歴閲覧
			'read withdrawal_aggregations', //払出し集計閲覧
			'export withdrawal_aggregations', //払出し集計エクスポート
			// add YuKaneko @2019/11/25
			'access daily_attendance_page', // 日毎一覧ページへアクセス
			// お知らせ
			'read messages', //閲覧
			'create messages', //登録
			'update messages', //編集
			'delete messages', //削除
			// trcd端末エラーログ
			'read trcd_messages', //閲覧
			// 勤務パターン
			// 休憩パターン
			// 有給パターン
			// 特殊勤怠パターン
			// trcd企業設定
			'update payroll_start_day', //払出初期日（締め日翌日） 更新
			'update withdraw_amount_limit_a_day', //全社員：払出上限金額 更新
			'update rounding_franction', //勤怠の切捨単位 更新
			// trcd端末への入出金
			'manage trcd_terminal', // trcd端末管理者
			// 残高不足通知
			'update trcd_terminal_notification_settings', 
		];
		$Permissions = $this->findPermissions($permission_names, 'trcd');

		if ( empty($role->syncPermissions($Permissions)) ) {
			logger()->error(__MEHTOD__ . " failed.");
			$this->__has_error = false;
		}
	}

	/*
		TRCDメッセージ管理者Roleに認可を設定
		@return void
	*/
	protected function syncPermissionsToTrcdMessageManager() {
		$role = $this->findRole('MESSAGE_MANAGER', 'trcd');
		
		if ( empty($role) ) return;

		$permission_names = [
			// 社員
			// パスワード
			'reset password', // パスワード再設定
			// 勤怠
			'read attendances', //閲覧
			'request attendances', //申請
			'request attendances', //申請
			'cancel attendances', //申請取消
			// add YuKaneko @2019/11/25
			'access daily_attendance_page', // 日毎一覧ページへアクセス
			// 出入金
			// お知らせ
			'read messages', //閲覧
			'create messages', //登録
			'update messages', //編集
			'delete messages', //削除
			// trcd端末エラーログ
			// 勤務パターン
			// 休憩パターン
			// 有給パターン
			// 特殊勤怠パターン
			// trcd企業設定
			// trcd端末への入出金
		];
		$Permissions = $this->findPermissions($permission_names, 'trcd');

		if ( empty($role->syncPermissions($Permissions)) ) {
			logger()->error(__MEHTOD__ . " failed.");
			$this->__has_error = false;
		}
	}

	/*
		TRCD経費登録Roleに認可を設定
		@return void
	*/
	protected function syncPermissionsToTrcdExpenseRegistration() {
		$role = $this->findRole('EXPENSE_REGISTRATION', 'trcd');

		if ( empty($role) ) return false;

		$permission_names = [
			'read expenses', //閲覧
			'create expenses', //登録
			'update expenses', //編集
			'delete expenses', //削除
			'request expenses', //申請
		];
		$Permissions = $this->findPermissions($permission_names, 'trcd');

		if ( empty($role->syncPermissions($Permissions)) ) {
			logger()->error(__MEHTOD__ . " failed.");
			$this->__has_error = false;
		}
	}

	/*
		TRCD経費承認Roleに認可を設定
		@return void
	*/
	protected function syncPermissionsToTrcdExpenseApproval() {
		$role = $this->findRole('EXPENSE_APPROVAL', 'trcd');

		if ( empty($role) ) return false;

		$permission_names = [
			'read expenses', //閲覧
			'update expenses', //編集
			'delete expenses', //削除
			'approve expenses', //承認
			'unlock expenses', //経費概要と仮払い金払い出し後の仮払い概要のロック解除
			'update account_titles', //勘定科目設定
			'read expense_aggregations', // 経費集計閲覧
			'export expense_aggregations', // 経費集計エクスポート
		];
		$Permissions = $this->findPermissions($permission_names, 'trcd');

		if ( empty($role->syncPermissions($Permissions)) ) {
			logger()->error(__MEHTOD__ . " failed.");
			$this->__has_error = false;
		}
	}

	/*
		TRCD経費管理Roleに認可を設定
		@return void
	*/
	protected function syncPermissionsToTrcdExpenseManager() {
		$role = $this->findRole('EXPENSE_MANAGER', 'trcd');
		
		if ( empty($role) ) return;

		$permission_names = [
			'read expenses', //閲覧
			'create expenses', //登録
			'update expenses', //編集
			'delete expenses', //削除
			'request expenses', //申請
			'unlock expenses', //経費概要と仮払い金払い出し後の仮払い概要のロック解除
			'update account_titles', //勘定科目設定
			'read expense_aggregations', // 経費集計閲覧
			'export expense_aggregations', // 経費集計エクスポート
		];
		$Permissions = $this->findPermissions($permission_names, 'trcd');

		if ( empty($role->syncPermissions($Permissions)) ) {
			logger()->error(__MEHTOD__ . " failed.");
			$this->__has_error = false;
		}
	}

	/*
		TRCD仮払い管理Roleに認可を設定
		@return void
	*/
	protected function syncPermissionsToTrcdTemporaryPaymentManager() {
		$role = $this->findRole('TEMPORARY_PAYMENT_MANAGER', 'trcd');

		if ( empty($role) ) return false;

		$permission_names = [
			'read temporary_payments', //閲覧
			'create temporary_payments', //登録
			'update temporary_payments', //編集
			'delete temporary_payments', //削除
			'request temporary_payments', //申請
			'approve temporary_payments', //承認
			'unlock temporary_payments', //仮払い金払い出し前の仮払い概要のロック解除
			'read temporary_payment_aggregations', // 仮払い金集計閲覧
			'export temporary_payment_aggregations', // 仮払い金集計エクスポート
			// 仮払い概要候補
			'read temporary_payment_summary_candidates', //閲覧
			'create temporary_payment_summary_candidates', //登録
			'update temporary_payment_summary_candidates', //編集
			'delete temporary_payment_summary_candidates', //削除
		];
		$Permissions = $this->findPermissions($permission_names, 'trcd');

		if ( empty($role->syncPermissions($Permissions)) ) {
			logger()->error(__MEHTOD__ . " failed.");
			$this->__has_error = false;
		}
	}

	/*
		TRCD勤怠申請のみ可能Roleに認可を設定
		@return void
	*/
	protected function syncPermissionsToTrcdAttendanceRequestOnly() {
		$role = $this->findRole('ATTENDANCE_REQUEST_ONLY', 'trcd');
		
		if ( empty($role) ) return;

		$permission_names = [
			// パスワード
			'reset password', // パスワード再設定
			// 勤怠
			'read attendances', //閲覧
			'request attendances', //申請
			'cancel attendances', //申請取消
		];
		$Permissions = $this->findPermissions($permission_names, 'trcd');

		if ( empty($role->syncPermissions($Permissions)) ) {
			logger()->error(__MEHTOD__ . " failed.");
			$this->__has_error = false;
		}
	}

	
	/*
		TRCD勤怠参照のみ可能Roleに認可を設定
		@return void
	*/
	protected function syncPermissionsToTrcdAttendanceReadingOnly() {
		$role = $this->findRole('ATTENDANCE_READING_ONLY', 'trcd');
		
		if ( empty($role) ) return;

		$permission_names = [
			// パスワード
			'reset password', // パスワード再設定
			// 勤怠
			'read attendances', //閲覧
		];
		$Permissions = $this->findPermissions($permission_names, 'trcd');

		if ( empty($role->syncPermissions($Permissions)) ) {
			logger()->error(__MEHTOD__ . " failed.");
			$this->__has_error = false;
		}
	}
}
