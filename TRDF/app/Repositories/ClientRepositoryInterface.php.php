<?php
/**
 * クライアントリポジトリインターフェイス
 *
 * @author T.Ando
 */

namespace App\Repositories;

interface ClientRepositoryInterface{

	/**
	 * IDから該当のデータを取得する
	 */
	public function getById($client_id);

	/**
	 * 社員IDから該当のデータを取得する
	 */
	//public function getByClientEmployeeId($client_employee_id);

	//public function create($client_employee_id, $withdraw_amount_allowable=null, $withdraw_amount_limit=null, $refund_amount_limit=null);

	/*
		ログインコード桁数取得
	*/
	public function getLoginCodeLength();

	/*
		ログインコード取得
	*/
	public function getAllLoginCodes($withTrashed);

	/*
		新規作成
	*/
	public function create($data);

	/*
		保存
	*/
	public function save($data);

	//public function delete($id);
}
