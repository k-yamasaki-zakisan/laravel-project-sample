<?php
/**
 * 経費所属グループリポジトリインターフェイス
 *
 * @author YuKaneko
 */

namespace App\Repositories\Trcd;

interface ExpenseGroupRepositoryInterface{
	public function getByClientId($client_id);

	/*
		指定されたクライアントIDの経費所属グループをリスト化して取得
	*/
	public function getListByClientId($client_id, $key = 'id', $value = 'name');

	public function create($data);

	public function save($data);

	public function delete($expense_group_id);

	/*
		「無所属」グループインスタンス生成
	*/
	public function generateUnaffiliatedGroup($client_id);
}
