<?php

namespace App\Http\Controllers\Superadmin\Licenses;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Redirect;

trait SelectExpotionTrait
{
    /*
		選択させたexpo_idを取得
		@return (int) $expo_id
	*/
    protected function getSelectExpotionId(Request $request)
    {
        try {
            $expo_id = $request->session()->get('superadmin_expo_selector');

            if (empty($expo_id)) throw new \RunTimeException("Exhibition = No select expotion");
        } catch (\Exception $e) {
            Redirect::route('superadmin.expositions.index')->with('flash_message', 'EXPOセレクタを選択ください')->send();
        }

        return (int) $expo_id;
    }
}
