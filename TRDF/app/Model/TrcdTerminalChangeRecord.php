<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;

class TrcdTerminalChangeRecord extends ModelBase
{
	use SoftDeletes;
	protected $dates = ['deleted_at'];

	protected $fillable = [
		'trcd_terminal_id',
		'trcd_terminal_change_type_id',
		'client_employee_id',
		'amount_of_change_total',
		'amount_of_change_10k',
		'amount_of_change_5k',
		'amount_of_change_1k',
		'amount_of_change_500',
		'amount_of_change_100',
		'amount_of_change_50',
		'amount_of_change_10',
		'amount_of_change_5',
		'amount_of_change_1',
		'amount_of_balance_total',
		'amount_of_balance_10k',
		'amount_of_balance_5k',
		'amount_of_balance_1k',
		'amount_of_balance_500',
		'amount_of_balance_100',
		'amount_of_balance_50',
		'amount_of_balance_10',
		'amount_of_balance_5',
		'amount_of_balance_1',
		'register_datetime',
	];

	public $validate = [
		'trcd_terminal_id' => ['bail', 'required', 'integer', 'exists:trcd_terminals,id'],
		'trcd_terminal_change_type_id' => ['bail', 'required', 'integer'], // コンストラクタ内でin条件追加
		'client_employee_id' => ['bail', 'required', 'integer', 'exists:client_employees,id'],
		'amount_of_change_total' => ['bail', 'required', 'integer'],
		'amount_of_change_10k' => ['bail', 'required', 'integer'],
		'amount_of_change_5k' => ['bail', 'required', 'integer'],
		'amount_of_change_1k' => ['bail', 'required', 'integer'],
		'amount_of_change_500' => ['bail', 'required', 'integer'],
		'amount_of_change_100' => ['bail', 'required', 'integer'],
		'amount_of_change_50' => ['bail', 'required', 'integer'],
		'amount_of_change_10' => ['bail', 'required', 'integer'],
		'amount_of_change_5' => ['bail', 'required', 'integer'],
		'amount_of_change_1' => ['bail', 'required', 'integer'],
		'amount_of_balance_total' => ['bail', 'required', 'integer'],
		'amount_of_balance_10k' => ['bail', 'required', 'integer'],
		'amount_of_balance_5k' => ['bail', 'required', 'integer'],
		'amount_of_balance_1k' => ['bail', 'required', 'integer'],
		'amount_of_balance_500' => ['bail', 'required', 'integer'],
		'amount_of_balance_100' => ['bail', 'required', 'integer'],
		'amount_of_balance_50' => ['bail', 'required', 'integer'],
		'amount_of_balance_10' => ['bail', 'required', 'integer'],
		'amount_of_balance_5' => ['bail', 'required', 'integer'],
		'amount_of_balance_1' => ['bail', 'required', 'integer'],
	];

	/*
		コンストラクタ
	*/
	public function __construct(array $attributes = []) {
		parent::__construct($attributes);

		$this->validate['trcd_terminal_change_type_id'][] = 'in:' . join(',', config('database.trcd.trcd_terminal_change_types.CONST'));
	}

	// リレーション
	// trcd_terminals
	public function trcd_terminal(){
		return $this->belongsTo('App\TrcdTerminal');
	}
}
