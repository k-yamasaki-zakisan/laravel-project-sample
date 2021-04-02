<?php

namespace App\Extensions\ValidationLogics;

use Illuminate\Validation\Rule;
use Carbon\Carbon;

trait BasicPatterns {
    /**
     * 半角数字しか含まれないことをバリデート
     * integer だと '001' を通さないので、代わりに使う
     *
     * @param $attribute  変数名               (ほぼ使わないけど順序的に省略できない...)
     * @param $value      値                   ★通常のチェック対象
     * @param $parameters ルールに与えられた変数（使わないなら省略していいかと）
     * @param $validator  バリデータインスタンス（まず使わないので省略しましょ）
     */
    public function number($attribute, $value, $parameters, $validator) {
        return preg_match('/^\s*[0-9]*\s*$/', $value);
    }

    /**
     * カタカナ
     */
    public function katakana($attribute, $value) {
/*
		$regex = '{^(
			(\xe3\x82[\xa1-\xbf]) # カタカナ
			|(\xe3\x83[\x80-\xbe]) # カタカナ
		)+$}x';
		return (1 === preg_match($regex, $value, $match)) ? true : false;
*/
        //return preg_match('/^[ァ-ヺーヽヾ\s]+$/u', $value); // Unicodeになってカタカナの範囲が広がったらしい
		// 全角数字も含める？
        return preg_match('/^[０-９ァ-ヺーヽヾ\s]+$/u', $value);
    }

	/*
		論理削除されているものを除いて存在確認
	*/
	public function exists_soft($attribute, $value, $parameters) {
        if (!isset($parameters[0])) {
            throw new \Exception('Validator "exists_soft" missing tablename.');
        }

		$tableName = $parameters[0];
		$columnName = isset($parameters[1])?$parameters[1]:null;

        $validator = \Validator::make([$attribute => $value],
        [
            $attribute => [
                Rule::exists($tableName, $columnName)->where(function ($query) {
					$query->whereNull('deleted_at');
				}),
            ]
        ]);

        return $validator->passes();
    }

	/*
		半角英数記号
	*/
	public function alpha_num_symbol($attribute, $value, $parameters, $validator) {
		$regex = '/^[!-~]+$/';
		return (1 === preg_match($regex, $value, $match)) ? true : false;
	}

	/*
		キー名を制限（厳密比較）
	*/
	public function key_in_strict($attribute, $value, $parameters, $validator) {
		$tmp_array = explode('.', $attribute);
		$tmp_key = $tmp_array[count($tmp_array) - 1];

		return in_array($tmp_key, $parameters, true);
	}

	/*
		null制約
	*/
	public function is_null($attribute, $value, $parameters, $validator) {
		return is_null($value);
	}

	/*
		link_keyフォーマット
	*/
	public function link_key($attribute, $value, $parameters, $validator) {
		$regex = '/^[0-9a-f]{64}$/';
		return (1 === preg_match($regex, $value, $match)) ? true : false;
	}

	/*
                電話番号の制約
        */
	public function phone_number($attribute, $value, $parameters, $validator) {
                $regex = '/^[0-9-]+$/';
		return (1 === preg_match($regex, $value, $match)) ? true : false;
	}

	/*
                資格取得日の制約(今年以内)
        */
	public function within_now_year($attribute, $value, $parameters, $validator) {
		$now_year = Carbon::now()->format('Y');
		return $value <= $now_year;
	}

    /*
        年月または年月日として有効な日付か確認
    */
	public function year_month_or_year_month_date($attribute, $value, $parameters, $validator) {
        // 年・月・日に対応するキー取得
        $keys = [
            'year' => $parameters[0] ?? null,
            'month' => $parameters[1] ?? null,
            'date' => $parameters[2] ?? null,
        ];
        // 検査対象データ取得
        $values = $validator->getData();

        $pattern = '/^[0-9]+$/';

        // 前提条件：年月に設定されているのは半角数字のみ
        if ( ! ( isset($values[$keys['year']]) && preg_match($pattern, $values[$keys['year']]) ) ) return false;
        if ( ! ( isset($values[$keys['month']]) && preg_match($pattern, $values[$keys['month']]) ) ) return false;

        // 整数化
        $year = intval($values[$keys['year']]);
        $month = intval($values[$keys['month']]);

        // 日にち処理
        if ( !isset($values[$keys['date']]) ) {
            // セットされていない場合は1日を仮代入して処理を行う
            $date = 1;
        } else if ( !preg_match($pattern, $values[$keys['date']]) ) {
            // 半角数字以外がセットされている場合は、false
            return false;
        } else {
            // 整数化
            $date = intval($values[$keys['date']]);
        }

        return checkdate($month, $date, $year);
    }
}