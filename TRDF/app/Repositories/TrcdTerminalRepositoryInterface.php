<?php
/**
 * TRCD端末リポジトリインターフェイス
 *
 * @author YuKaneko
 */

namespace App\Repositories\Trcd;

interface TrcdTerminalRepositoryInterface{
	public function create($data);
	public function save($data);
	public function delete($data);
}

