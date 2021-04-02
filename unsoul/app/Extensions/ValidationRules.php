<?php

namespace App\Extensions;

class ValidationRules {

    use ValidationLogics\BasicPatterns;

    public function getRules() {
                $class = get_class($this) . '@';

                return [
                        // 'rule名' => ['メソッド', 'メッセージ'], と書いていく
                        'number'   => [$class . 'number',   ":attributeには半角数字（0～9）のみが使用できます。"],
                        'katakana' => [$class . 'katakana', ":attributeには全角カタカナのみが使用できます。"],
                        'exists_soft' => [$class . 'exists_soft', ":attributeの値が不正です。"],
                        'alpha_num_symbol' => [$class . 'alpha_num_symbol', ":attributeは半角英数記号を入力してください。"],
                        'key_in_strict' => [$class . 'key_in_strict', "選択されたキーは正しくありません。"],
                        'is_null' => [$class . 'is_null', ":attributeに値が入力されています。"],
                        'link_key' => [$class . 'link_key', ":attributeのフォーマットが不正です。"],
                        'phone_number' => [$class . 'phone_number', ":attributeのフォーマットが不正です。"],
                        'within_now_year' => [$class . 'within_now_year', ":attributeは今年以内を入力ください。"],
                        'year_month_or_year_month_date' => [$class . 'year_month_or_year_month_date', "有効な日付ではありません。"],
                ];
    }
}