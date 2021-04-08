<?php

namespace App\Http\Requests\Libraries;

use App\Http\HttpCommonLib;
// model
use App\Product;

trait CheckSuitableForPlanTrait
{

    /*
	** 登録プランの登録可能な製品数を取得
	** return int
	*/
    protected function getProductPublishCount()
    {
        $objExhibitor = HttpCommonLib::GetExhibitorBySlugAndLoginUser();

        $product_publish_count = $objExhibitor->plan->product_publish_count;

        return $product_publish_count;
    }

    /*
	** 現在の有効化している製品数を取得
	** return int
	*/
    protected function getActiveProductCount()
    {
        $objExhibitor = HttpCommonLib::GetExhibitorBySlugAndLoginUser();

        $active_product_count = Product::where('exhibitor_id', $objExhibitor->id)->where('view_flag', true)->count();

        return $active_product_count;
    }
}
