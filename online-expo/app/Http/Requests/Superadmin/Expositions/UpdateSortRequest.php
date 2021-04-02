<?php

namespace App\Http\Requests\Superadmin\Exhibitions;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSortRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'sort_indexs' => '展示会と展示会ゾーン',
            'sort_indexs.*' => '展示会ソート順',
            'sort_indexs.*.*' => '展示会ゾーンソート順'
        ];
    }

    public function rules()
    {
        return [
            'sort_indexs' => ['bail', 'required', 'array'],
            'sort_indexs.*' => ['bail', 'required', 'array'],
            'sort_indexs.*.*' => ['bail', 'required', 'integer'],
        ];
    }

    public function messages()
    {
        return [
            'sort_indexs.required' => ':attributeは必須項目です',
            'sort_indexs.*.required' => ':attributeは必須項目です',
            'sort_indexs.*.*.required' => ':attributeは必須項目です',
            'sort_indexs.*.*.numeric' => '不正な操作です'
        ];
    }

    public function validated()
    {
        $validated = parent::validated();

        return [
            'sort_indexs' => $validated['sort_indexs']
        ];
    }
}
