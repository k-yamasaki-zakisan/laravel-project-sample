<?php

namespace App\Http\Requests\Employees;

use App\Http\Requests\RequestBase;

class UpdateEmployeeRequest extends RequestBase
{
    protected $rules = [
        'code' => ['bail', 'nullable', 'number'],
        'last_name' => ['bail', 'required', 'string'],
        'first_name' => ['bail', 'required', 'string'],
        'last_name_kana' => ['bail', 'required', 'string', 'katakana'],
        'first_name_kana' => ['bail', 'required', 'string', 'katakana'],
        'birthday' => ['bail', 'nullable', 'date_format:Y-m-d'],
        'gender_id' => ['bail', 'nullable', 'integer'],
        'employment_status_id' => ['bail', 'nullable', 'integer'],
        'zip_code1' => ['bail', 'nullable', 'digits:3'],
        'zip_code2' => ['bail', 'nullable', 'digits:4'],
        'prefecture_id' => ['bail', 'nullable', 'integer'],
        'city' => ['bail', 'nullable', 'string'],
        'town' => ['bail', 'nullable', 'string'],
        'street' => ['bail', 'nullable', 'string'],
        'building' => ['bail', 'nullable', 'string'],
        'address_kana' => ['bail', 'nullable', 'string', 'katakana'],
        'mobiles' => ['bail', 'nullable', 'array'],
        'mobiles.*' => ['bail', 'required', 'string'],
        'emails' => ['bail', 'nullable', 'array'],
        'emails.*' => ['bail', 'required', 'email'],
        'hire_date' => ['bail', 'nullable', 'date_format:Y-m-d'],
        'retirement_date' => ['bail', 'nullable', 'date_format:Y-m-d'],
        //'suspension' => ['bail', 'nullable', 'date_format:Y-m-d', 'required_with:employment_status_id'],
        //'reinstatement' => ['bail', 'nullable', 'date_format:Y-m-d', 'required_with:employment_status_id'],
        'basic_pension_number_1' => ['bail', 'nullable', 'string', 'size:4'],
        'basic_pension_number_2' => ['bail', 'nullable', 'string', 'size:6']
        //'basic_pension_number' => ['bail', 'nullable', 'array'],
        //'basic_pension_number.*' => ['bail', 'nullable', 'digits:1'],
    ];

    public function authorize()
    {
        return !empty(auth()->user());
    }

    public function attributes()
    {
        return [
            'employment_status_id' => '雇用形態',
            'prefecture_id' => '都道府県',
            'mobile' => '携帯',
            'email_address' => 'メールアドレス',
        ];
    }

    public function messages()
    {
        $messages = [
            'birthday.date_format' => ':attributeは年月日をY-m-d形式で指定してください。例）2020-09-01',
            'hire_date.date_format' => ':attributeは年月日をY-m-d形式で指定してください。例）2020-09-01',
            'retirement_date.date_format' => ':attributeは年月日をY-m-d形式で指定してください。例）2020-09-01',
            'suspension.date_format' => ':attributeは年月日をY-m-d形式で指定してください。例）2020-09-01',
            'reinstatement.date_format' => ':attributeは年月日をY-m-d形式で指定してください。例）2020-09-01',
        ];

        //for( $i = 1; $i <= 10; $i++ ) {
        //    $messages["basic_pension_number.{$i}.digits"] = "基礎年金番号[{$i}桁目]には半角数字１桁を入力してください。";
        //}
        return $messages;
    }

    //public function withValidator($validator) {
    //    $validator->after(function($validator) {
    //        // 基礎年金番号に未入力の桁数がある場合
    //        if ( !$this->filledInBasicPensionNumberProperly() ) {
    //            $validator->errors()->add('basic_pension_number', '基礎年金番号に未入力の桁があります。');
    //        }
    //    });
    //}

    /*
        基礎年金番号入力チェック
        @return bool
    */
    //protected function filledInBasicPensionNumberProperly() {
    //    $basic_pension_number = $this->input('basic_pension_number');
    //    $basic_pension_number = array_filter($basic_pension_number, function($item) {
    //        return !is_null($item);
    //    });

    //    if ( empty($basic_pension_number) ) return true;

    //    return count($basic_pension_number) === 10;
    //}

    public function validated()
    {
        $validated = parent::validated();

        // 基礎年金番号連結・フォーマット整形
        //$basic_pension_number = $this->input('basic_pension_number');
        //$basic_pension_number = array_filter($basic_pension_number, function($item) {
        //    return !is_null($item);
        //});
        //$validated['joined_basic_pension_number'] = empty($basic_pension_number)
        //    ? null
        //: substr_replace(join('', $basic_pension_number), '-',4, 0)
        //    :join('', $basic_pension_number)
        //;
        $validated['joined_basic_pension_number'] = $validated['basic_pension_number_1'] . $validated['basic_pension_number_2'];

        return $validated;
    }
}
