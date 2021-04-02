<?php
/***
 * 勤怠生ログ用サービス
 *
 * @author T.Ando
 */

namespace App\Services\Trcd;

// Services
use App\Services\ServiceBase;
// Repositories
use App\Repositories\Trcd\TrcdAlcoholCheckRecordRepositoryInterface AS TrcdAlcoholCheckRecordRepository;
// Utilities
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use DB;

class AlcoholCheckRecordService extends ServiceBase
{
	protected $objTrcdAlcoholCheckRecordRepository;

	public function __construct(TrcdAlcoholCheckRecordRepository $objTrcdAlcoholCheckRecordRepository){
		$this->objTrcdAlcoholCheckRecordRepository = $objTrcdAlcoholCheckRecordRepository;
	}


	/*
		アルコール検査履歴登録
		@param int $trcd_terminal_id
		@param int $client_employee_id
		@param Array $data
		@return  Model|false
	*/
	public function add($trcd_terminal_id, $client_employee_id, Array $data) {
		DB::beginTransaction();

		try {
			$base64_image = null;

			if ( !empty($data['base64_image']) ) $base64_image = $data['base64_image'];

			unset($data['base64_image']);

			// レコード登録
			$TrcdAlcoholCheckRecord = $this->objTrcdAlcoholCheckRecordRepository->create($trcd_terminal_id, $client_employee_id, $data);

			if ( empty($TrcdAlcoholCheckRecord) ) throw new \Exception("Failed to add TrcdAlcoholCheckRecord." . print_r($data, true));

			// 画像格納処理
			if ( !empty($base64_image) ) {
				$store_result = $this->storeBase64Image($base64_image, $client_employee_id, Carbon::parse($data['checked_datetime'])->format('Ymd-His'));

				if ( empty($store_result) ) throw new \Exception("Failed to store TrcdAlcoholCheckRecord image.");
			}

			DB::commit();
		} catch( \Exception $e ) {
			DB::rollBack();
			logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
			return false;
		}

		return $TrcdAlcoholCheckRecord;
	}

	/*
		Base64画像データを格納
		@throws Exception
		@param string $base64
		@param int $client_employee_id
		@param string $file_name
		@return int|bool
	*/
	public function storeBase64Image($base64, $client_employee_id, $file_name) {
		$Disk = Storage::disk('local');
		$dirpath = "alcohol_check_images/" . $client_employee_id;

		// 格納先がない場合は作成
		if ( !$Disk->exists($dirpath) ) {
			$Disk->makeDirectory($dirpath, 0775, true);
		}

		$decoded = base64_decode($base64);
		//$type = finfo_buffer(finfo_open(), $decoded, FILEINFO_EXTENSION);
		// jpgで送信されてきているので暫定的に拡張子は固定
		$filepath = "{$dirpath}/{$file_name}.jpg";

		return $Disk->put($filepath, $decoded);
	}
}