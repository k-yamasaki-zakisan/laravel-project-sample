<?php

namespace App\Http\Requests\Superadmin\Exhibitors;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExhibitorsRequest extends FormRequest
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

    public function attributes()
    {
        return [
            'name' => '出展社',
            'name_kana' => '出展社カナ',
            'name_kana_for_sort' => '出展企業名カナ',
            'company_id' => '会社名',
            'exhibition_id' => '出展展示会',
            'exhibition_zone_id' => '出展ゾーン',
            'zip_code1' => '郵便番号1',
            'zip_code2' => '郵便番号2',
            'prefecture_id' => '都道府県',
            'address' => '所在地',
            'building_name' => '建物名',
            'tel' => '電話番号',
            'url' => 'リンク',
            'profile_text' => 'プロフィール',
            'plan_id' => '出展プラン'
        ];
    }


    public function rules()
    {
        return [
            'name' => ['bail', 'required', 'max:200'],
            'name_kana' => ['bail', 'required', 'max:200'],
            'name_kana_for_sort' => ['bail', 'required', 'max:200'],
            'exhibition_id' => ['bail', 'required', 'exists:exhibitions,id'],
            'exhibition_zone_id' => ['bail', 'required', 'exists:exhibition_zones,id'],
            'company_id' => ['bail', 'required', 'integer'],
            'zip_code1' => ['bail', 'required', 'digits:3'],
            'zip_code2' => ['bail', 'required', 'digits:4'],
            'prefecture_id' => ['bail'],
            'address' => ['bail', 'required', 'max:200'],
            'building_name' => ['bail', 'max:200'],
            'tel' => ['bail', 'required', 'regex:/^[0-9]{2,4}-[0-9]{2,4}-[0-9]{3,4}$/'],
            'url' => ['bail', 'url', 'max:250'],
            'profile_text' => ['bail', 'nullable'],
            'plan_id' => ['bail', 'required', 'exists:plans,id'],
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':attributeは必須項目です',
            '*.max' => '文字数が多すぎます。',
            '*.regex' => '半角数字とハイフンを使って入力して下さい',
            '*.exists' => ':attributeが不正な値です',
            'zip_code1.digits' => '3桁の半角数字で入力して下さい',
            'zip_code2.digits' => '4桁の半角数字で入力して下さい',
            'url.url' => '正しい形式で入力して下さい',
        ];
    }

    public function withValidator($validator)
    {

        $validator->after(function ($validator) {
            // 企業プロフィールを取得
            $profile_text = $this->input('profile_text');
            // htmlタグを削除
            $profile_text = strip_tags($profile_text);
            // 改行文字を削除
            $profile_text = str_replace("\r\n", '', $profile_text);
            // 特殊文字を削除
            $profile_text = str_replace(['<br>', '&nbsp;', '&emsp;', '&ensp;'], '', $profile_text);
            // スペースの削除(半角,全角)
            $profile_text = str_replace(array(" ", ""), "", $profile_text);
            // 文字数のカウント
            $char_count = mb_strlen($profile_text);
            // 文字数が2000を超えていた場合
            if ($char_count >= 2000) {
                $validator->errors()->add('profile_text', '文字数が上限を超えています');
            }
        });
    }

    public function validated()
    {

        // リクエストデータを取得（validate済み）
        $validated = parent::validated();

        return [
            'name' => $validated['name'],
            'name_kana' => $validated['name_kana'],
            'name_kana_for_sort' => $validated['name_kana_for_sort'],
            'exhibition_id' => $validated['exhibition_id'],
            'exhibition_zone_id' => $validated['exhibition_zone_id'],
            'company_id' => $validated['company_id'],
            'zip_code1' => $validated['zip_code1'],
            'zip_code2' => $validated['zip_code2'],
            'prefecture_id' => $validated['prefecture_id'],
            'address' => $validated['address'],
            'building_name' => $validated['building_name'],
            'tel' => $validated['tel'],
            'url' => $validated['url'],
            'profile_text' => $validated['profile_text'],
            'plan_id' => $validated['plan_id'],
        ];
    }
}
