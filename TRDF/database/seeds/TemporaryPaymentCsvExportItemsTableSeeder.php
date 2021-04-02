<?php

use Illuminate\Database\Seeder;

use App\Repositories\Trcd\TemporaryPaymentCsvExportItemRepositoryInterface as TemporaryPaymentCsvExportItemRepository;
use App\TemporaryPaymentCsvExportItem;

class TemporaryPaymentCsvExportItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @author YuKaneko 2020.02.26
     * config/database/trcd/expense_csv_items.phpに記載されている設定内容に依存する
     * 記載されていないものがDB上に存在する場合は削除
     * keyとvalueの組み合わせが設定ファイルとDB上で異なるものは更新
     * DB上に存在しないkeyが設定ファイルに記載されている場合は挿入
     */
    public function run(TemporaryPaymentCsvExportItemRepository $objTemporaryPaymentCsvExportItemRepo)
    {
		info(__METHOD__);
		// 道具と材料を用意
		$EXPORT_TYPES = config('database.trcd.csv_export_types_v2.CONST'); // 種別
		$CSV_ITEMS = [
			'AGGREGATION' => config('database.trcd.expense_csv_items.AGGREGATION'), // 集計項目
			'DETAIL' => config('database.trcd.expense_csv_items.DETAIL.TEMPORARY_PAYMENT'), // 明細項目
		];
		$ColTemporaryPaymentCsvExportItems = $objTemporaryPaymentCsvExportItemRepo->all(); // DB上のレコード

		$ColDeleteTargets = collect(); // 削除対象
		$ColUpdateTargets = collect(); // 更新対象
		$ColInsertTargets = collect(); // 挿入対象

		foreach( $ColTemporaryPaymentCsvExportItems as $idx => $objItem ) {
			$tmp_items = null;

			switch($objItem->export_type_id) {
				case($EXPORT_TYPES['AGGREGATION']):
					$tmp_items = $CSV_ITEMS['AGGREGATION'];
					break;
				case($EXPORT_TYPES['DETAIL']):
					$tmp_items = $CSV_ITEMS['DETAIL'];
					break;
				default:
					break;
			}

			// 不正なレコードは削除する
			if ( empty($tmp_items) ) {
				logger()->warning("不正なexport_type_id {$objItem->export_type_id} を保持しているレコードがあります。このレコードは削除されます。"
					. print_r($objItem->toArray(), true)
				);
				$ColDeleteTargets->put($idx, $ColTemporaryPaymentCsvExportItems->pull($idx));
				continue;
			}

			// 設定ファイルに記載されていないkeyは削除対象
			if ( !isset($tmp_items[$objItem->key]) ) {
				$ColDeleteTargets->put($idx, $ColTemporaryPaymentCsvExportItems->pull($idx));
				continue;
			}

			// DB・設定ファイルともに記載されているkeyで、nameが異なるものは更新対象 
			$objItem['name'] = $tmp_items[$objItem->key];

			if ( $objItem->isDirty() ) {
				$ColUpdateTargets->put($idx, $objItem);
				continue;
			}
		}


		// 挿入検索
		foreach( $CSV_ITEMS as $export_type_name => $classified_csv_items ) {
			// 出力種別ごとの現在のkey一覧を抽出
			$tmp_export_type_id = $EXPORT_TYPES[$export_type_name];
			$tmp_current_keys = $ColTemporaryPaymentCsvExportItems->where('export_type_id', $tmp_export_type_id)
				->pluck('key', 'key');

			foreach( $classified_csv_items as $key => $name ) {
				// key一覧に載っていなければ挿入対象
				if ( !isset($tmp_current_keys[$key]) ) {
					$objNew = new TemporaryPaymentCsvExportItem();
					$objNew->export_type_id = $tmp_export_type_id;
					$objNew->key = $key;
					$objNew->name = $name;

					$ColInsertTargets->push($objNew);
				}
			}
		}

		info('削除対象');
		info($ColDeleteTargets->toArray());
		info('更新対象');
		info($ColUpdateTargets->toArray());
		info('挿入対象');
		info($ColInsertTargets->toArray());

		DB::beginTransaction();
		try {
			// 削除処理
			if ( !$ColDeleteTargets->isEmpty() ) {
				$delete_result = $objTemporaryPaymentCsvExportItemRepo->delete($ColDeleteTargets->pluck('id')->toArray());

				if ( empty($delete_result) ) throw new Exception("削除処理に失敗しました。");
			}

			// 更新処理
			if ( !$ColUpdateTargets->isEmpty() ) {
				foreach( $ColUpdateTargets as $objUpdateTarget ) {
					$update_result = $objTemporaryPaymentCsvExportItemRepo->save($objUpdateTarget->toArray());

					if ( empty($update_result) ) throw new Exception("更新処理に失敗しました。");
				}
			}

			// 挿入処理
			if ( !$ColInsertTargets->isEmpty() ) {
				foreach( $ColInsertTargets as $objInsertTarget ) {
					$insert_result = $objTemporaryPaymentCsvExportItemRepo->save($objInsertTarget->toArray());

					if ( empty($insert_result) ) throw new Exception("挿入処理に失敗しました。");
				}
			}

			DB::commit();
		} catch( Exception $e ) {
			DB::rollBack();
			logger()->error("[仮払いCSV項目Seeder処理] 失敗しました。");
			throw $e;
		}
    }
}
