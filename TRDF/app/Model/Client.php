<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

//class Client extends Model
class Client extends ModelBase
{
	// 論理削除
	use SoftDeletes;

	const LOGIN_CODE_LENGTH = 9;//ログインコード桁数

	protected $fillable = [
		"login_code",
		"name"
	];

	protected $dates = ['deleted_at'];

	// hasMany
	public function client_branches() {
		return $this->hasMany('App\ClientBranch');
	}	

	public function client_users() {
		return $this->hasMany('App\ClientUser');
	}

	public function client_employees() {
		return $this->hasMany('App\ClientEmployee');
	}

	public function client_settings() {
		return $this->hasMany('App\ClientSetting');
	}

	public function companies() {
		return $this->hasMany('App\Company');
	}

	public function locations() {
		return $this->hasMany('App\Location');
	}

	public function expense_groups() {
		return $this->hasMany(ExpenseGroup::class);
	}

	public function expense_group_settings() {
		return $this->hasMany(ExpenseGroupSetting::class);
	}

	public function temporary_payment_summary_candidates() {
		return $this->hasMany(TemporaryPaymentSummaryCandidate::class);
	}

	// belongsTo
	public function prefecture() {
		return $this->belongsTo('App\Prefecture');
	}

	// hasOne
	public function client_trcd_setting() {
		return $this->hasOne('App\ClientTrcdSetting');
	}

	// hasOne
        public function balance_threshold() {
                return $this->hasOne('App\BalanceThreshold');
        }

	// belongsToMany
	public function account_titles() {
		return $this->belongsToMany('App\AccountTitle', 'account_title_clients', 'client_id', 'account_title_id');
	}

	public function contract_types() {
		return $this->belongsToMany(ContractType::class, 'client_contract_types', 'client_id', 'contract_type_id');
	}

	/**
		* バリデーション
		* @var array
	*/
	public $validate = [
		'login_code' => ['required', 'string', 'max:20'],
		'name' => ['required', 'string', 'max:100'],
		'phonetic' => ['string', 'max:255', 'nullable'],
		'email' => ['string', 'max:255', 'nullable'],
		'zip1' => ['digits:3', 'nullable'],
		'zip2' => ['digits:4', 'nullable'],
		'town' => ['string', 'max:100', 'nullable'],
		'street' => ['string', 'max:100', 'nullable'],
		'building' => ['string', 'max:50', 'nullable'],
		'tel' => ['string', 'max:20', 'nullable'],
		'fax' => ['string', 'max:20', 'nullable'],
		'emergency_contact' => ['string', 'max:50', 'nullable'],
		'auth_key' => ['required', 'alpha_num', 'size:32'],
	];

	/*
		新規追加用バリデーションルール取得
		@param Array $data
		@return Array $validation_rules
	*/
	public function buildValidationRulesForInsert($data) {
		$validation_rules = $this->validate;

		//auth_keyはシステム全体で一意（論理削除されているものも含む）
		$validation_rules['auth_key'][] = Rule::unique('clients');

		return $validation_rules;
	}

	/*
		更新用バリデーションルール取得
		@param Array $data
		@return Array $validation_rules
	*/
	public function buildValidationRulesForUpdate($data) {
		$validation_rules = $this->validate;

		//auth_keyはシステム全体で一意（論理削除されているものも含む） 自分は無視
		$validation_rules['auth_key'][] = Rule::unique('clients')->ignore($data['id']);

		return $validation_rules;
	}

	/*
		指定されたキーの契約を締結しているか
		一つでも該当するものがあればtrue
		@param Array $contract_key_names
		@return bool
	*/
	public function hasAnyContracts(Array $contract_key_names) {
		$contract_list = $this->contract_types->pluck('name', 'key');

		foreach( $contract_key_names as $contract_key_name ) {
			if ( isset($contract_list[$contract_key_name]) ) return true;
		}

		return false;
	}
}
