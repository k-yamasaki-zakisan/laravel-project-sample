<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseSummary extends ModelBase
{
	use SoftDeletes;

	protected $fillable = [
		'requested_at',
		'client_employee_id',
		'content',
		'total_amount',
		'account_title_id',
		'account_title_name',
		'type_id',
		'temporary_payment_amount',
		'is_draft',
		'request_flag',
		'approval_flag',
		'has_paid_out',
		'has_refunded_surplus',
		'has_paid_out_excess',
		'created_by',
		'updated_by',
		'is_temporary_payment_total_amount_fixed',
		'gap',
		'paid_out_at',
		'temporary_payment_registered_at',
		'status_code',
		'refunded_surplus_at',
		'paid_out_excess_at',
		'locked_at',
		'receipts_registered_by',
		'expected_use_date_of_temporary_payment',
		'approved_by',
		'have_all_original_receipts_been_received',
	];

	public $validate = [
		'requested_at' => ['bail', 'nullable', 'date_format:"Y-m-d H:i:s"'],
		'client_employee_id' => ['bail', 'required', 'integer', 'min:1'],
		'content' => ['bail', 'required', 'string', 'max:255'],
		'total_amount' => ['bail', 'required', 'integer'],
		'account_title_id' => ['bail', 'nullable', 'integer', 'exists:account_titles,id'],
		'account_title_name' => ['bail', 'nullable', 'string', 'max:32'],
		'type_id' => ['bail', 'required', 'integer', 'in:1,2'],
		'temporary_payment_amount' => ['bail', 'required', 'integer'],
		'is_draft' => ['bail', 'required', 'boolean'],
		'request_flag' => ['bail', 'required', 'boolean'],
		'approval_flag' => ['bail', 'required', 'boolean'],
		'has_paid_out' => ['bail', 'required', 'boolean'],
		'has_refunded_surplus' => ['bail', 'required', 'boolean'],
		'has_paid_out_excess' => ['bail', 'required', 'boolean'],
		'created_by' => ['bail', 'required', 'integer', 'min:1'],
		'updated_by' => ['bail', 'nullable', 'integer', 'min:1'],
		'is_temporary_payment_total_amount_fixed' => ['bail', 'required', 'boolean'],
		'gap' => ['bail', 'nullable', 'integer'],
		'paid_out_at' => ['bail', 'nullable', 'date_format:"Y-m-d H:i:s"'],
		'temporary_payment_registered_at' => ['bail', 'nullable', 'date_format:"Y-m-d H:i:s"'],
		'status_code' => ['bail', 'nullable', 'integer'],
		'refunded_surplus_at' => ['bail', 'nullable', 'date_format:"Y-m-d H:i:s"'],
		'paid_out_excess_at' => ['bail', 'nullable', 'date_format:"Y-m-d H:i:s"'],
		'locked_at' => ['bail', 'nullable', 'date_format:"Y-m-d H:i:s"'],
		'receipts_registered_by' => ['bail', 'nullable', 'integer', 'min:1'],
		'expected_use_date_of_temporary_payment' => ['bail', 'nullable', 'date_format:"Y-m-d"'],
		'approved_by' => ['bail', 'nullable', 'integer', 'min:1'],
		'have_all_original_receipts_been_received' => ['bail', 'required', 'boolean'],
	];


	public function client_employee() {
		return $this->belongsTo('App\ClientEmployee', 'client_employee_id');
	}

	public function account_title() {
		return $this->belongsTo('App\AccountTitle');
	}

	public function created_by() {
		return $this->belongsTo('App\ClientEmployee', 'created_by');
	}

	public function updated_by() {
		return $this->belongsTo('App\ClientEmployee', 'updated_by');
	}

	public function expense_headers() {
		return $this->hasMany('App\ExpenseHeader');
	}

	/*
		経費としてフィルタリング
		種別IDが経費または、仮払いで仮払い出金済み
	*/
	public function scopeCanBeHandledAsExpense($query) {
		$EXPENSE_SUMMARY_TYPE_CONSTANTS = config('database.trcd.expense_summary_types.CONST');

		return $query->where(function($query) use($EXPENSE_SUMMARY_TYPE_CONSTANTS) {
			$query->where('type_id', $EXPENSE_SUMMARY_TYPE_CONSTANTS['EXPENSE'])
				->orWhere(function($query) use ($EXPENSE_SUMMARY_TYPE_CONSTANTS) {
					$query->where('type_id', $EXPENSE_SUMMARY_TYPE_CONSTANTS['TEMPORARY_PAYMENT'])
						->where('has_paid_out', true);
				});
		});
	}

	/*
		新規追加用バリデーションルール取得
		@param Array $data
		@return Array $validation_rules
	*/
	public function buildValidationRulesForInsert($data) {
		$validation_rules = $this->validate;

		return $validation_rules;
	}

	/*
		更新用バリデーションルール取得
		@param Array $data
		@return Array $validation_rules
	*/
	public function buildValidationRulesForUpdate($data) {
		$validation_rules = $this->validate;

		return $validation_rules;
	}

	/*
		ライフサイクル
	*/
	public static function boot() {
		parent::boot();

		/*
			保存前のイベント（登録・更新含む）
		*/
		self::saving(function($model){
			$model->status_code = $model->CalcStatusCode();
		});
	}

	private function CalcStatusCode() {
		/*
			is_draft                                1桁目 下書き 1
			request_flag                            2桁目 申請 2
			approval_flag                           3桁目 承認 4
			is_temporary_payment_total_amount_fixed 4桁目 仮払い時レシート登録 8
			has_paid_out                            5桁目 払出し済み 16
			isset(gap) && gap < 0                   6桁目 余剰金発生 32
			has_refunded_surplus                    7桁目 余剰金返却済み 64
			isset(gap) && gap > 0                   8桁目 超過金発生 128
			has_paid_out_excess                     9桁目 超過金払出し済み 256
		*/    
		$status_code = 0;

		if ( !empty($this->is_draft) )                                $status_code += bindec('000000001');
		if ( !empty($this->request_flag) )                            $status_code += bindec('000000010');
		if ( !empty($this->approval_flag) )                           $status_code += bindec('000000100');
		if ( !empty($this->is_temporary_payment_total_amount_fixed) ) $status_code += bindec('000001000');
		if ( !empty($this->has_paid_out) )                            $status_code += bindec('000010000');
		if ( isset($this->gap) && $this->gap < 0 )                    $status_code += bindec('000100000');
		if ( !empty($this->has_refunded_surplus) )                    $status_code += bindec('001000000');
		if ( isset($this->gap) && $this->gap > 0 )                    $status_code += bindec('010000000');
		if ( !empty($this->has_paid_out_excess) )                     $status_code += bindec('100000000');

		return $status_code;
	}

	/*
		経費払出し可能判定
		@return bool
	*/
	public function CanWithdrawExpense() {
		return $this->status_code === config('database.trcd.expense_summary_status_codes.CONST.EXPENSE.HAS_NOT_PAID_OUT');
	}

	/*
		経費払出し済み判定
		@return bool
	*/
	public function HasWithdrawnExpense() {
		return $this->status_code === config('database.trcd.expense_summary_status_codes.CONST.EXPENSE.HAS_PAID_OUT');
	}

	/*
		仮払い金払出し可能判定
		@return bool
	*/
	public function CanWithdrawTemporaryPayment() {
		return $this->status_code === config('database.trcd.expense_summary_status_codes.CONST.TEMPORARY_PAYMENT.HAS_NOT_PAID_OUT');
	}

	/*
		仮払い金払出し済み判定
		@return bool
	*/
	public function HasWithdrawnTemporaryPayment() {
		return $this->status_code === config('database.trcd.expense_summary_status_codes.CONST.TEMPORARY_PAYMENT.HAS_PAID_OUT');
	}

	/*
		仮払い金超過分払出し可能判定
		@return bool
	*/
	public function CanWithdrawExcess() {
		return $this->status_code === config('database.trcd.expense_summary_status_codes.CONST.TEMPORARY_PAYMENT.HAS_NOT_PAID_OUT_EXCESS');
	}

	/*
		仮払い金超過分払出し済み判定
		@return bool
	*/
	public function HasWithdrawnExcess() {
		return $this->status_code === config('database.trcd.expense_summary_status_codes.CONST.TEMPORARY_PAYMENT.HAS_PAID_OUT_EXCESS');
	}

	/*
		仮払い金余剰分返金可能判定
		@return bool
	*/
	public function CanRefundSurplus() {
		return $this->status_code === config('database.trcd.expense_summary_status_codes.CONST.TEMPORARY_PAYMENT.HAS_NOT_REFUNDED_SURPLUS');
	}

	/*
		仮払い金余剰分返金済み判定
		@return bool
	*/
	public function HasRefundedSurplus() {
		return $this->status_code === config('database.trcd.expense_summary_status_codes.CONST.TEMPORARY_PAYMENT.HAS_REFUNDED_SURPLUS');
	}

	/*
		申請中（承認待ち）判定
	*/
	public function isBeingRequested() {
		$list = $this->getStatusCodeListOfBeingRequested();
if ( isset($list[0]) ) logger('isset($list[0])');
		return isset($list[$this->status_code]);
	}

	public function getStatusCodeListOfBeingRequested() {
		$config = config('database.trcd.expense_summary_status_codes');
		$list = array_only($config['LIST'], [
			$config['CONST']['EXPENSE']['REQUEST'],
			$config['CONST']['TEMPORARY_PAYMENT']['TOTAL_AMOUNT_REQUEST'],
			$config['CONST']['TEMPORARY_PAYMENT']['TOTAL_AMOUNT_REQUEST_WITH_SURPLUS'],
			$config['CONST']['TEMPORARY_PAYMENT']['TOTAL_AMOUNT_REQUEST_WITH_EXCESS'],
		]);

		return collect($list);
	}
}