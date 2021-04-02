<?php

namespace App\Http\Requests\Trcd;

//use Illuminate\Foundation\Http\FormRequest;
use App\ClientEmployee;
use Carbon\Carbon;

class AlcoholCheckRecordEntryPostRequest extends TrcdFormRequestBase
{
	public $rules = [
		'user_id' => [ 'bail','required','exists:client_employees,auth_key', ],
		'date' => ['bail', 'required', 'date_format:"Y/m/d"'],
		'time' => ['bail', 'required', 'date_format:"H:i:s"'],
		'result' => ['bail', 'required', 'in:OK,NG'],
		'measured_value' => ['bail', 'nullable', 'numeric', 'regex:/^0\.[0-9]{2}$/'],
		'threshold' => ['bail', 'required', 'numeric', 'regex:/^0\.[0-9]{2}$/'],
		'status' => ['bail', 'required', 'integer'],
		'attendance_raw_id' => ['bail', 'nullable', 'integer', 'exists:attendance_raws,id'],
		'base64_image' => ['bail', 'nullable', 'string'],
	];

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return parent::rules();
	}


	public function validated() {
		$validated = parent::validated();

		$validated['checked_datetime'] = Carbon::parse("{$validated['date']} {$validated['time']}")->format('Y-m-d H:i:s');
		unset($validated['date'], $validated['time']);

		$validated['result_flag'] = $validated['result'] === 'OK';
		unset($validated['result']);

		return $validated;
	}
}