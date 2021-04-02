<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Gender extends ModelBase
{
	use SoftDeletes;

	protected $primaryKey = 'gender_id';
	protected static $_cachedTableColumns; // テーブルカラムキャッシュ格納用

	public function scopeLinkKey($query, $key) {
		return $query->where('link_key', $key);
	}

	public function scopeApiResponse($query) {
		return $query->select(['name', 'link_key'])
			->orderBy('sort_index', 'asc')
			->orderBy($this->primaryKey, 'asc');
	}

  public function getTableColumns($use_cache = true) {
    // 未キャッシュフラグOFFの場合、キャッシュ更新
    if ( empty(SELF::$_cachedTableColumns) || empty($use_cache) ) {
      SELF::$_cachedTableColumns = $this->_getColumnListing($this->getTable());
    }

    return SELF::$_cachedTableColumns;
  }

}