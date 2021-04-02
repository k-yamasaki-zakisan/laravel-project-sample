<?php

namespace App\Http\Requests\Licenses;

use Carbon\Carbon;

trait AcquisitionTrait {
    /*
        取得日を生成
        @return Carbon
    */
    protected function buildAcquisition($year, $month, $date) {
        $pattern = '/^[0-9]+$/';

        // 前提条件：年月に設定されているのは半角数字のみ
        if ( ! ( isset($year) && preg_match($pattern, $year) ) ) return null;
        if ( ! ( isset($month) && preg_match($pattern, $month) ) ) return null;

        // 整数化
        $year = intval($year);
        $month = intval($month);

        // 日にち処理
        if ( !isset($date) ) {
            // セットされていない場合は1日を仮代入して処理を行う
            $date = 1;
        } else if ( !preg_match($pattern, $date) ) {
            // 半角数字以外がセットされている場合は、nullを返す
            return null;
        } else {
            // 整数化
            $date = intval($date);
        }

        // 有効な日付でない場合は、nullを返す
        if ( !checkdate($month, $date, $year) ) return null;

        // Carbon生成
        return Carbon::create($year, $month, $date)->startOfDay();
    }
}