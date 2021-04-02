<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class BalanceThreshold extends ModelBase
{
	use SoftDeletes;

	protected $fillable = [
		'client_id',
		'lower_threshold_1yen',
		'lower_threshold_5yen',
		'lower_threshold_10yen',
		'lower_threshold_50yen',
		'lower_threshold_100yen',
		'lower_threshold_500yen',
		'lower_threshold_1k',
		'lower_threshold_5k',
		'lower_threshold_10k',
	];


        //belongsTo
        public function client()
        {
                return $this->belongsTo('App\Client');
        }       
                
        /**     
        * バリデーション
        * @var array
        */
        protected $rules = [
		'client_id' => ['required', 'integer', 'min:0', 'exists:clients,id',],
                'lower_threshold_1yen' => ['integer', 'min:0', 'nullable'],
                'lower_threshold_5yen' => ['integer', 'min:0', 'nullable'],
                'lower_threshold_10yen' => ['integer', 'min:0', 'nullable'],
                'lower_threshold_50yen' => ['integer', 'min:0', 'nullable'],
                'lower_threshold_100yen' => ['integer', 'min:0', 'nullable'],
                'lower_threshold_500yen' => ['integer', 'min:0', 'nullable'],
                'lower_threshold_1k' => ['integer', 'min:0', 'nullable'],
                'lower_threshold_5k' => ['integer', 'min:0', 'nullable'],
                'lower_threshold_10k' => ['integer', 'min:0', 'nullable'],
	];

	/*
		新規作成用ルール
	*/
	public function buildValidationRulesForCreate($data) {
		$rules = $this->rules;

		// client_idはユニークであること
		$rules['client_id'][] = Rule::unique('balance_thresholds');
			//->whereNull('deleted_at');

		return $rules;
	}

	/*
                更新用ルール
        */
        public function buildValidationRulesForUpdate($data) {
		$rules = $this->rules;

		// id必須
		$rules['id'] = ['bail', 'required', 'integer', 'min:1'];
		$ruels['id'][] = Rule::exists('balance_thresholds')->whereNull('deleted_at');

                // client_idはユニークであること
		$rules['client_id'][] = Rule::unique('balance_thresholds')
			->whereNull('deleted_at')
			->ignore($data['id']);

                return $rules;
        }
	

}
