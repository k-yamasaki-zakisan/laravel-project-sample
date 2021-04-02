<?php
/**
 * TRCD端末
 * @author YuKaneko
 */

namespace App\Infrastracture\Repositories\Trcd;

use App\Repositories\Trcd\TrcdTerminalRepositoryInterface;
use App\Infrastracture\Repositories\BaseRepository;

use App\TrcdTerminal;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;


use Validator;

class TrcdTerminalRepository extends BaseRepository implements TrcdTerminalRepositoryInterface {

	//利用するモデルのクラス指定
	protected static $modelClass = \App\TrcdTerminal::class;

	public function create($data) {
		$objTrcdTerminal = new TrcdTerminal();
		$data = Arr::except($data, ['id']);

		$validator = $this->Validator($data, $objTrcdTerminal->validate);

		if ( $validator->fails() ) return false;

		foreach($data as $key => $value){
			$objTrcdTerminal->$key = $value;
		}

		// SQL実行
		try{
			return $objTrcdTerminal->save() ? $objTrcdTerminal : false;
		}catch(\Exception $e){
			Log::Error($e->getMessage());
			return false;
		}
	}

	public function save($data) {
		if ( !isset($data['id']) ) return $this->create($data);

		$objTrcdTerminal = $this->find($data['id']);

		foreach($data as $key => $value){
			$objTrcdTerminal->$key = $value;
		}

		$tmpData = $objTrcdTerminal->toArray();
		$validator = $this->Validator($tmpData, $objTrcdTerminal->validate);

		if ( $validator->fails() ) return false;

		// SQL実行
		try{
			return $objTrcdTerminal->save() ? $objTrcdTerminal : false;
		}catch(\Exception $e){
			Log::Error($e->getMessage());
			return false;
		}
	}

	/*
		IDを指定して削除
		@return bool
	*/
	public function delete($trcd_terminal_id){
    try{
			$this->where('id', $trcd_terminal_id)->delete();
    }catch(\Exception $e){
      Log::Error($e->getMessage());
      return false;
    }

    return true;
	}
}
