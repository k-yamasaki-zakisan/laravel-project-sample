<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class ApiBaseController extends Controller {

/*
	APIレスポンス用配列を生成
	@param $response_data
	@param Array $extra_summary_data summaryマージ用追加データ
	@param int $response_code
	@return Array
*/
	protected function _buildResponseArray($response_data, Array $extra_summary_data = [], $response_code = 200) {
        $result = [
			'summary' => [
				'status' => $response_code,
			],
            'data' => $response_data,
		];

		if ( !empty($extra_summary_data) ) $result['summary'] = array_merge($result['summary'], $extra_summary_data);

        return $result;
    }
}
