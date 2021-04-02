<?php

namespace App\Http\Controllers\Api\Trcd;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class TrcdApiBaseController extends Controller
{

	//protected $_ObjDb;
	//protected $_strResultJsonData;
	//protected $_clientId;
	//protected $_error;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
//print __METHOD__."()<br />";
		//parent::__construct();
		$this->middleware(function($request, $next){
			// 認証失敗していたらエラーコードを返す。
			if(!Auth::check()){
				$this->outputError('400', '認証に失敗しました。');
				exit();
			}

			return $next($request);
		});

	}



	protected function output(){
print "TODO: ";
print __METHOD__."()";
exit();
	}
	protected function outputError($responseCode, $strErrorMessage=''){
		$aryResponse = $this->buildResponseArrayError($responseCode, $strErrorMessage);
		//response()->json($aryResponse);
		//print json_encode($aryResponse);
		//exit();

		// 少々強引だが。
		foreach( preg_split('/\n/', response()->json($aryResponse)) as $header_line ){
			if(empty(trim($header_line))){break;}
			//if(strlen($header_line) <= 1){break;}
			header($header_line);
		}
		print json_encode($aryResponse);
		exit();
	}

	/**
	 * エラーの場合のレスポンス配列変数を形成する。
	 *
	 * @return array
	 */
	public function buildResponseArrayError($responseCode, $strErrorMessage=''){
		$result = array();
		$result['status'] = $responseCode;
		if(!empty($strErrorMessage)){
			$result['errors'] = $strErrorMessage;
		}

		return $result;
	}
	/**
	 * TRCD向けAPIレスポンス配列変数を形成する。
	 *
	 * @return array
	 */
	public function buildResponseArray($aryResponseData=array(), $responseCode = '200'){
		$result = array();
		$result['status'] = $responseCode;
		if(!empty($aryResponseData)){
			$result['data'] = $aryResponseData;
		}

		return $result;
	}




	/**
	 * 認証情報からtrcd_terminalを返す。（ClientBranch、Client付き）
	 *
	 * @return trcdterminal | null
	 */
	protected function _getTrcdTerminal(){ return Auth::user(); }



	/**
	 * Logの共通関数
	 *
	 * @param string $message
	 */
	protected function LogError($message){
		Log::error('TRCD API: '.$message);
	}


}