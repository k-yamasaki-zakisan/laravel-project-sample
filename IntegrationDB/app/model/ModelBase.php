<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Schema;
use DateTimeInterface;

define('SQL_LOG', true);

class ModelBase extends Model{

	/**
	 * コンストラクタ
	 */
	public function __construct(array $attributes = []){
		parent::__construct($attributes);

		// SQLログを出力
		ModelBase::QueryLog();
	}

	/**
	 * クエリログを出力
	 */
	public static function QueryLog(){
		static $init = false;

		if ($init) {
			return true;
		}

		if( defined('SQL_LOG') && SQL_LOG ){
			DB::listen(function ($query) {
				logger("Query Time:{$query->time}ms] $query->sql");
			});
		}

		$init = true;
	}

	/*
		指定テーブルのカラム配列取得
		@param string $table
		@return Array
	*/
	protected function _getColumnListing($table) {
		return Schema::getColumnListing($table);
	}

	/*
		論理削除用トレイトを利用判定
		@return bool
	*/
	public function useSoftDeletes() {
		return in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this));
	}

	/**
	 * 配列／JSONシリアライズのためデータを準備する
	 *
	 * @param  \DateTimeInterface  $date
	 * @return string
	 */
	protected function serializeDate(DateTimeInterface $date) {
		return $date->format('Y-m-d H:i:s');
	}
}
