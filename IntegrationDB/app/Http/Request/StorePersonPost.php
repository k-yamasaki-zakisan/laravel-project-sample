<?php

namespace App\Http\Requests\API\Person;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\Gender;
use App\Services\RandomValueGenerator;


class StorePersonPost extends FormRequest
{
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
        $rules = [
			'login_id' => ['required', 'max:255', 'email','unique:persons' ],
			'password' => ['required', 'min:8', 'alpha_num_symbol'],
			'last_name' => ['required', 'max:64', 'string'],
			'first_name' => ['required', 'max:64', 'string'],
			'last_name_kana' => ['required', 'max:64', 'string', 'katakana'],
			'first_name_kana' => ['required', 'max:64', 'string', 'katakana'],
			'gender' => ['nullable'],
			'birthday' => ['nullable', 'date'],
			'passed_away_at' => ['nullable', 'date'],
        ];

		$rules['gender'][] = Rule::exists('genders', 'link_key')->where(function($query) {
			$query->whereNull('deleted_at');
		});

		return $rules;
    }

	public function validated() {
		$validated = parent::validated();
		$date_format = 'Y-m-d';

		if ( isset($validated['password']) ) $validated['password'] = Hash::make($validated['password']);
		if ( isset($validated['birthday']) ) $validated['birthday'] = Carbon::parse($validated['birthday'])->format($date_format);;
		if ( isset($validated['passed_away_at']) ) $validated['passed_away_at'] = Carbon::parse($validated['passed_away_at'])->format($date_format);;
		if ( isset($validated['gender']) ) {
			$Gender = Gender::where('link_key', $validated['gender'])->first();

			if ( !empty($Gender) ) $validated['gender_id'] = $Gender->gender_id;

			unset($validated['gender']);
		}
		$validated['last_updated_system_id'] = auth()->user()->system_id;
		$validated['link_key'] = RandomValueGenerator::generateLinkKey();

		return $validated;
	}
}
