<?php

namespace App\Services;

use App\Services\ServiceBase;

use App\Exposition;

use Carbon\Carbon;
use DateTime;

class UserService extends ServiceBase
{


    /**
     * その展示会で事前登録済みのユーザーかを確認する
     *
     * @return boolean 登録済みのユーザーであればtrueを返す。そうでなければ false を返す
     */
    public static function isEntryUser(int $user_id, int $expo_id)
    {
        // 事前登録が完了しているユーザーか
        foreach (Exposition::GetEntryUsers($expo_id) as $objUser) {
            if ($user_id == $objUser->id) {
                return true;
            }
        }

        return false;
    }
    public static function isEntryUserBySlug(int $user_id, string $expo_slug)
    {
        $objExposition = Exposition::Active()->where('slug', $expo_slug)->first();
        if ($objExposition == null) {
            return false;
        }

        return self::isEntryUser($user_id, $objExposition->id);
    }

    /*
	** 全てのEXPOから登録ユーザーであるかを確認
	 */
    public static function isEntryUserBySlugWithAllStatusExposition(int $user_id, string $expo_slug)
    {
        $objExposition = Exposition::where('slug', $expo_slug)->first();
        if ($objExposition == null) {
            return false;
        }

        return self::isEntryUser($user_id, $objExposition->id);
    }


    /**
     * ユーザーIDとEXPO idを元に出展社の担当ユーザーであるかを確認
     */
    public static function isExhibitorUserByExpoId(int $user_id, int $expo_id)
    {
        // $objExposition = Exposition::Active()->where('id', $expo_id)->first();
        $objExposition = Exposition::where('id', $expo_id)->first();
        if ($objExposition == null) {
            return false;
        }

        // 指定ユーザーが出展社アカウントでアクセスできる展示会かを確認する
        $user_ids = [];
        foreach ($objExposition->exhibitions as $objExhibition) {
            foreach ($objExhibition->exhibitors as $objExhibitor) {
                foreach ($objExhibitor->users as $objUser) {
                    if (!in_array($objUser->id, $user_ids)) {
                        $user_ids[] = $objUser->id;
                    }
                }
            }
        }

        if (!in_array($user_id, $user_ids)) {
            return false;
        }

        return true;
    }

    public static function checkSessionTime(string $expo_slug)
    {

        $Exposition = Exposition::where('slug', $expo_slug)->firstOrFail();
        // 終了期間の調整(-1)
        $exposition_days = $Exposition->exposition_days - 1;
        // 終了期間
        $finish_date = date('Y-m-d', strtotime("+{$exposition_days} day", strtotime($Exposition->start_date)));
        // 会期開始時間から1時間引く
        $session_start_time = date('H:i', strtotime('-1 hour', strtotime($Exposition['session_start_time'])));
        // 会期終了時間から1時間足す
        $session_end_time = date('H:i', strtotime('+1 hour', strtotime($Exposition['session_end_time'])));
        // 現在時刻の取得
        $now = Carbon::now();

        // EXPO開始時間の結合
        $exposition_start_date = $Exposition['start_date'] . ' ' . $session_start_time;
        // EXPO終了時間の結合
        $exposition_end_date = $finish_date . ' ' . $session_end_time;

        // 日付比較の為
        $ExpositionStartDate = new DateTime($exposition_start_date);
        $ExpositionEndDate = new DateTime($exposition_end_date);

        // 会期時間外の場合
        if ($ExpositionStartDate > $now || $now > $ExpositionEndDate) {
            return true;
        }

        return false;
    }
}
