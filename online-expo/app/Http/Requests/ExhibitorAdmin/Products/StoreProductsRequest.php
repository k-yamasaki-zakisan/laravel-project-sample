<?php

namespace App\Http\Requests\ExhibitorAdmin\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

use Carbon\Carbon;
// Traits
use \App\Http\Requests\Libraries\CheckSuitableForPlanTrait;

class StoreProductsRequest extends FormRequest
{
    use CheckSuitableForPlanTrait;

    public function attributes()
    {
        return [
            'product_name' => '出展製品名',
            'product_description' => '製品説明',
            'product_view_flag' => '製品有効化フラグ'
        ];
    }

    public function rules()
    {
        return [
            'product_name' => ['bail', 'required', 'string', 'max:200'],
            'product_description' => ['bail', 'required', 'string', 'max:1000'],
            'product_view_flag' => ['bail', 'nullable', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':attributeは必須項目です',
            '*.max' => '文字が多すぎます',
            'product_view_flag.boolean' => '不正な値です',
        ];
    }

    public function withValidator(Validator $validator)
    {

        $validator->after(function ($validator) {
            if ($this->product_view_flag) {
                $product_publish_count = $this->getProductPublishCount();
                $active_product_count = $this->getActiveProductCount();
                if ($product_publish_count <= $active_product_count) {
                    $validator->errors()->add('product_view_flag', 'ご契約プランの有効製品数を超えております');
                }
            }
        });
    }

    public function validated()
    {
        $validated = parent::validated();

        return [
            'name' => $validated['product_name'],
            'description' => $validated['product_description'],
            'view_flag' => $validated['product_view_flag'] ?? false
        ];
    }
}
