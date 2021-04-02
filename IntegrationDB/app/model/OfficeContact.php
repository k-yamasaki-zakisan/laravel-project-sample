<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class OfficeContact extends ModelBase
{
	use SoftDeletes;
	protected $primaryKey = 'office_contact_id';
  protected static $_cachedTableColumns; // テーブルカラムキャッシュ格納用
  /*
    テーブルカラム配列取得
    @param bool $use_cache キャッシュ利用フラグ
    @return Array
  */
  public function getTableColumns($use_cache = true) {
    // 未キャッシュフラグOFFの場合、キャッシュ更新
    if ( empty(SELF::$_cachedTableColumns) || empty($use_cache) ) {
      SELF::$_cachedTableColumns = $this->_getColumnListing($this->getTable());
    }

    return SELF::$_cachedTableColumns;
  }
}
