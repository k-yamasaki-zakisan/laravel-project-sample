<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\MessageBag;

abstract class UnSoulBaseController extends Controller
{
	/*
		@param string $key
		@param mix $value
		@return void
	*/
	protected function flashOldInput($key, $value) {
		session()->flash('_old_input', [$key => $value]);
	}

	/*
		UUID生成
	*/
	protected function _generateUuid() {
		return (string) Str::uuid();
	}

	/*
		（簡易的な）URL一致判定
		@throws InvalidArgumentException schemeとhostは必須
		@param string $url
		@param string $another_url
		@param bool $include_params クエリパラメータまで含めて判定をするか
		@return bool
	*/
	protected function isSameURLBetween($url, $another_url, $include_params = false) {
		$urls = [
			parse_url($url),
			parse_url($another_url),
		];

		foreach( $urls as $idx => $item ) {
			// schemeとhostは必須
			if ( ! ( isset($item['scheme']) && isset($item['host']) ) ) throw new \InvalidArgumentException("Scheme and host is required.");

			$tmp = "{$item['scheme']}://{$item['host']}";

			if ( isset($item['path']) ) $tmp .= "{$item['path']}";
			// クエリパラメータ
			if ( $include_params && isset($item['query']) ) $tmp .= "{$item['query']}";

			$urls[$idx]['url'] = $tmp;
		}

		return $urls[0]['url'] === $urls[1]['url'];
	}

	/*
		MessageBag生成
		@param Array $messages
		@return Illuminate\Support\MessageBag
	*/
	protected function createMessageBag(Array $messages = []) {
		return new MessageBag($messages);
	}

    /*
        LoginUserの情報を配列で取得
        @return null|Array
    */
    protected function _getArrLoginUser() {
        $LoginUser = auth()->user();

        return empty($LoginUser) ? null : $LoginUser->toArray();
    }
}