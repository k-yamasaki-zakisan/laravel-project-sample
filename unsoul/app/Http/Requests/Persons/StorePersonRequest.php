<?php

namespace App\Http\Requests\Persons;

use App\Http\Requests\RequestBase;
use Illuminate\Http\Request;

class StorePersonRequest extends RequestBase
{
	protected $redirectRoute = 'unsoul.persons.register';
	public $rules = [
		'corporation_name' => ['bail', 'required', 'string'],
		'login_id' => ['bail', 'required', 'string', 'max:255'],
		'password' => ['bail', 'required', 'string', 'min:8', 'max:64', 'confirmed'],
		'password_confirmation' => ['bail', 'required'],
		'last_name' => ['bail', 'required', 'string', 'max:64'],
		'first_name' => ['bail', 'required', 'string', 'max:64'],
		'last_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
		'first_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
		'gender_id' => ['bail', 'nullable', 'integer'],
		'birthday' => ['bail', 'nullable', 'date_format:Y-m-d'],
	];
}
