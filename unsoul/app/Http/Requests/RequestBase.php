<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class RequestBase extends FormRequest
{
	// 全リクエスト共通のルール格納用配列
	private $__base_rules = [];
	// 継承先で追加するルール格納用配列
	protected $rules = [];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //return false;
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
		// 全リクエスト共通ルールと継承先ルールをマージ
		// 同一のキーがあれば継承先ルールで上書き
		return array_merge($this->__base_rules, $this->rules);
    }
}
