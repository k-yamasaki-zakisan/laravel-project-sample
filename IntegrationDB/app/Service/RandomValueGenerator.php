<?php

namespace App\Services;

use Illuminate\Support\Str;

class RandomValueGenerator {

	/*
		整数の乱数生成
		@param bool $with_hyphen ハイフン有りフラグ
		@return string
	*/
	public static function generateUuid($with_hyphen = true) {
		$uuid = Str::uuid();

		if ( $with_hyphen ) return $uuid->__toString();
		else return Str::of($uuid)->replace('-', '')->__toString();;
	}

	/*
		連携キー生成
		@return string
	*/
	public static function generateLinkKey() {
		$link_key = SELF::generateUuid(false);
		$link_key .= SELF::generateUuid(false);

		return $link_key;
	}

	/*
		整数の乱数生成
		@throws LogicException
		@param $digit 桁数
		@param $zero_padding 0埋めフラグ
		@return int or string
	*/
	public static function generateRandomNumber($digit, $zero_padding = true) {
		if ( $digit < 1 ) throw new \LogicException("\$digit must be grater than 0, {$digit} was given.");

		// コードの最大値算出
		$max = pow(10, $digit) - 1;
		// 乱数生成
		$rand = random_int(0, $max);

		// 乱数の頭0埋め
		if ( $zero_padding ) return sprintf("%0{$digit}d", $rand);
		else return $rand;
	}
}