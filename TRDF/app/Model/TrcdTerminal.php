<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrcdTerminal extends Authenticatable {
	// 論理削除
	use SoftDeletes;
	protected $dates = ['deleted_at'];

	// Validation
	public $validate = [
		'auth_code' => ['required', 'string', 'size:32', 'regex:/^[0-9a-zA-Z\-_]{32}$/'],
		'name' => ['required', 'string', 'max:128'],
	];

	// コンストラクタ
	public function __construct() {
		parent::__construct();

		ModelBase::QueryLog();
	}

	// リレーション
	// client_branches
	public function client_branches(){
		return $this->belongsToMany('App\ClientBranch');
	}

	// trcd_terminal_change_records
        public function trcd_terminal_change_records(){
                return $this->hasMany('App\TrcdTerminalChangeRecord');
        }

        // trcd_terminal_notification_setting
        public function trcd_terminal_notification_settings(){
                return $this->hasMany('App\TrcdTerminalNotificationSetting');
	}

	public function getClientIds(){
		$result = array();
		foreach($this->client_branches as $client_branch){
			$result[] = $client_branch->client_id;
		}
		return $result;
	}

}
