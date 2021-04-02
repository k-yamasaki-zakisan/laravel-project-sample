<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Repositories\Trcd\BalanceThresholdRepositoryInterface AS BalanceThresholdRepository;

class BalanceThresholdsTableSeeder extends Seeder
{
	protected $BalanceThresholdRepository;
	public function __construct(
		BalanceThresholdRepository $BalanceThresholdRepository
	){
		$this->BalanceThresholdRepository = $BalanceThresholdRepository;
	}
	/**
		* Run the database seeds.
	*/
	public function run() {
		//DB上に登録してあるbalance_thresholdsのclient_idを集める
		$not_create_client_ids = DB::table('balance_thresholds')->orderBy('client_id')->pluck('client_id');
		$create_client_ids = DB::table('clients')->whereNotIn('id', $not_create_client_ids)->orderBy('id')->pluck('id');

print('重複しているもの（すでに存在しているので今回は挿入しないもの）--------------------------------------------------'.PHP_EOL);
var_dump($not_create_client_ids);
print('登録対象(balance_thresholdsに紐づいていないclientのため新規のbalance_thresholdsをDBに登録するもの)--------------------------------------------------'.PHP_EOL);
var_dump($create_client_ids);
		/*
			balance_thresholdsにないclient_idをもつ企業を登録
		*/
		DB::beginTransaction();
		try {
			if ( !empty($create_client_ids) ) {
				foreach ( $create_client_ids as $client_id ) {
					//var_dump($client_id);
					$result = $this->BalanceThresholdRepository->create($client_id);

					if ( empty($result) ) {
						$err_msg = "BalanceThresholdの作成に失敗。client_id = {$client_id}";
						logger()->error($err_msg);
						throw new \Exception($err_msg);
					}
				}
			}
			DB::commit();
		} catch(\Exception $e) {
			var_dump($e->getMessage());
			DB::rollBack();

			return;
		}
    }
}
