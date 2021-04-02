<?php
/***
 * BalanceThresholSettingサービス
 *
 * @author K.Yamasaki
 */

namespace App\Services\Trcd;

use App\Services\ServiceBase;
use App\Repositories\Trcd\BalanceThresholdRepositoryInterface AS BalanceThresholdRepository;

class BalanceThresholdService extends ServiceBase
{
        protected $objBalanceThresholdRepository;

        public function __construct(BalanceThresholdRepository $objBalanceThresholdRepository){
                $this->objBalanceThresholdRepository = $objBalanceThresholdRepository;
        }

        /*
                @param $data
                @return 成功:結果配列 失敗:false
        */
        public function create($client_id) {
                return $this->objBalanceThresholdRepository->create($client_id);
        }

        public function getByClientId($client_id) {
                return $this->objBalanceThresholdRepository->where('client_id', $client_id)->get();
        }

}
