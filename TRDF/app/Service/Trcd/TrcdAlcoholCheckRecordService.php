<?php
/***
 * アルコール検査履歴サービス
 * @author YuKaneko
 */

namespace App\Services\Trcd;

// Services
use App\Services\ServiceBase;
// Repositories
use App\Repositories\Trcd\TrcdAlcoholCheckRecordRepositoryInterface as TrcdAlcoholCheckRecordRepository;

class TrcdAlcoholCheckRecordService extends ServiceBase {

	protected $TrcdAlcoholCheckRecordRepo;

	public function __construct(
		TrcdAlcoholCheckRecordRepository $TrcdAlcoholCheckRecordRepo
	){
		$this->TrcdAlcoholCheckRecordRepo = $TrcdAlcoholCheckRecordRepo;
	}

	/*
		@param Array $conditions
		@return Illuminate\Database\Eloquent\Builder
	*/
	public function buildSearchQuery(Array $conditions = []) {
		$Query = $this->TrcdAlcoholCheckRecordRepo->query();

		// 出力項目制限
		if ( isset($conditions['select']) ) $Query->select($conditions['select']);

		// TRCD端末ID制限
		if ( isset($conditions['trcd_terminal_id']) ) {
			$method = is_array($conditions['trcd_terminal_id']) ? 'whereIn' : 'where';
			$Query->$method('trcd_terminal_id', $conditions['trcd_terminal_id']);
		}

		// 社員制限
		if ( isset($conditions['client_employee_id']) ) {
			$method = is_array($conditions['client_employee_id']) ? 'whereIn' : 'where';
			$Query->$method('client_employee_id', $conditions['client_employee_id']);
		}

		// 開始日
		if ( !empty($conditions['from']) ) $Query->where('checked_datetime', '>=', $conditions['from']);
		// 終了日
		if ( !empty($conditions['until']) ) $Query->where('checked_datetime', '<=', $conditions['until']);

		// 関連情報
		if ( !empty($conditions['with']) ) $Query->with($conditions['with']);

		return $Query;
	}
}