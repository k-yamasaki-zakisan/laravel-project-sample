<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitorsRequest extends FormRequest
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
        return [
            'zip_code1' => ['bail', 'required', 'numeric', 'digits:3'], // zip_code1
            'zip_code2' => ['bail', 'required', 'numeric', 'digits:4'], // zip_code2
            'prefecture_id' => ['bail', 'required', 'numeric'], // 都道府県ID
            'address' => ['bail', 'required', 'max:200'], // 住所
            'building_name' => ['bail', 'max:200'], // 住所
            'tel' => ['bail', 'required', 'regex:/^[0-9]{2,4}-[0-9]{2,4}-[0-9]{3,4}$/'], // TEL
            'url' => ['bail', 'nullable', 'url', 'max:300'], // URL
            'profile_text' => ['bail', 'max:2000'], // プロフィールテキスト
        ];
    }

    public function messages()
    {
        return [
            'zip_code1.numeric' => '郵便番号1が不正です。整数で入力してください。',
            'zip_code1.digits' => '郵便番号1が不正です。3桁と4桁で入力してください。',
            'zip_code1.required' => '郵便番号1が未入力です。入力して下さい。',
            'zip_code2.numeric' => '郵便番号2が不正です。整数で入力してください。',
            'zip_code2.digits' => '郵便番号2が不正です。3桁と4桁で入力してください。',
            'zip_code2.required' => '郵便番号2が未入力です。入力して下さい。',
            'address.required' => '住所が未入力です。入力して下さい。',
            'address.max' => '住所の文字数が制限を超ています。200文字以内で入力してください。',
            'building_name.max' => '住所の文字数が制限を超ています。200文字以内で入力してください。',
            'tel.required' => '電話番号が未入力です。入力してください。',
            'tel.regex' => '電話番号に不正な文字が含まれています。半角数字とハイフンのみが利用できます。',
            'url.url' => 'URLに入力された値が不正です。URL表記で入力してください。',
            'profile_text.max' => '企業プロフィールの文字数が制限を超ています。2000文字以内で入力してください。',
        ];
    }
}
