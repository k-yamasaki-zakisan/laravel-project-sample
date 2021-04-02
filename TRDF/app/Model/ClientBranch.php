<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

//class ClientBranch extends Model
class ClientBranch extends ModelBase
{

	// 論理削除
	use SoftDeletes;
	protected $dates = ['deleted_at'];

  /**
   * バリデーション
   * @var array
   */
  public $validate = [
		'client_id' => ['required', 'integer', 'min:1'],
		'name' => ['required', 'string', 'max:100'],
    'phonetic' => ['string', 'max:255', 'nullable'],
    'email' => ['string', 'max:255', 'nullable'],
    'tel' => ['string', 'max:20', 'nullable'],
    'zip1' => ['digits:3', 'nullable'],
    'zip2' => ['digits:4', 'nullable'],
    'town' => ['string', 'max:100', 'nullable'],
    'street' => ['string', 'max:100', 'nullable'],
    'building' => ['string', 'max:50', 'nullable'],
    'fax' => ['string', 'max:20', 'nullable'],
    'emergency_contact' => ['string', 'max:50', 'nullable'],
  ];

	// リレーション
	// clients
	public function client(){
		return $this->belongsTo('App\Client');
	}

	// trcd_terminals
	public function trcd_terminals(){
		return $this->belongsToMany(TrcdTerminal::class);
	}

}
