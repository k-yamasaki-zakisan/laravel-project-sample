<?php
/**
 * 経費所属グループ設定リポジトリインターフェイス
 *
 * @author YuKaneko
 */

namespace App\Repositories\Trcd;

interface ExpenseGroupSettingRepositoryInterface{
	public function getByClientId($client_id);

	public function getListByClientId($client_id, $key = 'id', $value = 'name');

	public function create(Array $data);

	public function save($data);

	public function delete($id);

	public function generateInstanceByClientId($client_id);
}
