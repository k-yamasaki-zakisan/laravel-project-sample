<?php

/**
 * HTTP向け共通関数
 */

namespace App\Http;


use Illuminate\Routing\Route;
use App\Exposition;
use App\Exhibitor;
use App\User;
use Auth;


class HttpCommonLib
{

    /**
     * URLからスラッグを取得
     */
    public static function GetSlug()
    {
        static $expo_slug = null;

        if ($expo_slug === null) {
            $allRouteParams = \Request::route()->parameters();
            if (isset($allRouteParams['expo_slug']) && !empty($allRouteParams['expo_slug'])) {
                $expo_slug = $allRouteParams['expo_slug'];
            } else {
                $expo_slug = '';
            }
        }

        return $expo_slug;
    }

    /**
     * URLスラッグから該当のExpoを取得。該当のアクティブなExpoが無ければnullを返す
     */
    public static function GetExposition()
    {
        static $objExposition = null;

        $expo_slug = self::GetSlug();
        if (empty($expo_slug)) {
            return false;
        }

        if ($objExposition == null) {
            $objExposition = Exposition::Active()->where('slug', $expo_slug)->first();
            if ($objExposition == null) {
                return false;
            }
        }
        return $objExposition;
    }




    /**
     * スラッグとログインユーザーから該当のexhibitorを取得します
     * @return object
     */
    public static function GetExhibitorBySlugAndLoginUser()
    {
        // スラッグを元に Expositionを取得
        $objExposition = HttpCommonLib::GetExposition();
        $aryPossibilityExhibitionIds = [];
        foreach ($objExposition->exhibitions as $objExhibition) {
            $aryPossibilityExhibitionIds[] = $objExhibition->id;
        }

        $aryPossibilityExhibitorIds = [];
        foreach (Auth::user()->exhibitors as $objExhibitorWk) {
            $aryPossibilityExhibitorIds[] = $objExhibitorWk->id;
        }

        return Exhibitor::whereIn('exhibition_id', $aryPossibilityExhibitionIds)->whereIn('id', $aryPossibilityExhibitorIds)->first();
    }
}
