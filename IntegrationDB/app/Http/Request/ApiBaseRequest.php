<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiBaseRequest extends FormRequest
{

	// 全リクエスト共通ルールがあれば追加
	private $__base_rules = [];
	// 継承先で追加されるルール
	protected $rules = [];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
		return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
		// 全リクエスト共通ルールと継承先ルールのマージ
		// 同一のキーがあれば継承先ルールで上書き
		return array_merge($this->__base_rules, $this->rules);
    }

    /**
     * バリデーション失敗時の処理
     * @override
     * @param Validator $validator
     * @throw HttpResponseException
     */
    protected function failedValidation(Validator $validator) {
		$error = array_merge(config('constants.errors.BAD_REQUEST'), ['occured_at' => now()->format('Y-m-d H:i:s')]);
		$response = [
			'summary' => [
				'status' => 400,
				'error' => $error,
			],
			'parameter_errors' => $validator->errors()->toArray(),
		];

        throw new HttpResponseException(
            response()->json($response, 200)
        );
    }

	/*
		ルールの追加
		@param Array $base 追加される側のルール
		@param Array $extra 追加するルール
		@return void
	*/
	protected function _addRulesOn(Array &$base, Array $extra) {
		$base = array_merge($base, $extra);
	}

	/*
		1以上bigint上限(9223372036854775807)以下の整数値用ルールを取得
		主にプライマリキーに利用
	*/
	protected function _getNaturalBigIntegerRules() {
		return ['numeric', 'min:1', 'max:' . PHP_INT_MAX, 'integer'];
	}

	/*
		1以上int上限(2147483647)以下の整数値用ルールを取得
		主にプライマリキーに利用
	*/
	protected function _getNaturalIntegerRules() {
		return ['numeric', 'min:1', 'max:2147483647', 'integer'];
	}

	/*
		1以上int上限(32767)以下の整数値用ルールを取得
		主にプライマリキーに利用
	*/
	protected function _getNaturalSmallIntegerRules() {
		return ['numeric', 'min:1', 'max:32767', 'integer'];
	}
}
