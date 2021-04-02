<?php

namespace App\Http\Requests\ExhibitorAdmin\ExhibitorImages;

use Illuminate\Foundation\Http\FormRequest;

use Carbon\Carbon;

class StoreExhibitorImagesRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'exhibitor_image' => '出展企業画像'
        ];
    }

    public function rules()
    {
        return [
            'exhibitor_image' => ['bail', 'required', 'image', 'mimes:jpeg,png,jpg', 'max:2048']
        ];
    }

    public function messages()
    {
        return [
            '*.required' => ':attributeは必須項目です',
            'exhibitor_image.image' => ':attributeはjpeg,png,jpg形式が有効です'
        ];
    }
}
