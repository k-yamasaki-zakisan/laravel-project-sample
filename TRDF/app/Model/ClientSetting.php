<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;

class ClientSetting extends ModelBase
{
		//belongsTo
		public function client()
		{
				return $this->belongsTo('App\Client');
		}


		/**
		 * バリデーション
		 * @var array
		 */
		public $validate = [
			'client_id' => ['required', 'numeric'],
			'google_maps_api_key' => ['string', 'nullable'],
			'consumption_tax' => ['bail', 'integer', 'min:0'],
			'consumption_tax_rounding_type_id' => ['bail', 'required', 'integer', 'min:0'],
			'total_amount_rounding_type_id' => ['bail', 'required', 'integer', 'min:0'],
			'edit_flag' => ['boolean'],
		];
}

