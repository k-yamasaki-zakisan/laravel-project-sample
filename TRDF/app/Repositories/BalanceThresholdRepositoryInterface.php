<?php
/**
 * 残高閾値の設定用リポジトリインターフェイス
 *
 * @author K.Yamasaki
 */

namespace App\Repositories\Trcd;

interface BalanceThresholdRepositoryInterface{
        public function create($client_id);

        public function deleteByClientId($client_id);
}
