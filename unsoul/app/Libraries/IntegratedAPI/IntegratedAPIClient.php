<?php
namespace App\Libraries\IntegratedAPI;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

// Utilities
use Illuminate\Support\Str;
// Exceptions
use Illuminate\Http\Client\ConnectionException;
use App\Libraries\IntegratedAPI\Exceptions\IntegratedAPIConnectionException;

class IntegratedAPIClient {

	/*
		エンドポイントのキーを指定してリクエスト
		@param string $key
		@param Array $data
		@return IntegratedAPIResponse
	*/
	public function requestByKey($key, Array $data = []) {
		$endpoint = $this->getEndpointByKey($key);

		return $this->request($endpoint['method'], $endpoint['path'], $data);
	}

	/*
		リクエスト処理
		@throws IntegratedAPIConnectionException
		@param string $method
		@param string $path
		@param Array $data
		@return IntegratedAPIResponse
	*/
	protected function request($method, $path, Array $data = []) {
		// システムキー付与
		$data['system_key'] = $this->getSystemKey();

		// HTTPメソッド設定
		$method = $this->convertMethod($method);

		// エンドポイント設定
		$endpoint = $this->generateAbsEndpoint($path);

		// クライアント生成
		$Client = $this->generateClient();

		try {
			// リクエスト送信
			$response = $Client->$method($endpoint, $data);

			// レスポンスが取得出来なかった場合は例外
			if ( empty($response) ) throw new \RuntimeException("Integrated API response is empty.");

		} catch ( ConnectionException $e ) {
			// 接続エラー（タイムアウトや接続先が見つからないなど）
			list($err_code, $err_msg) = config('integrated_api.errors.CONNECTION');
			throw (new IntegratedAPIConnectionException($err_msg, $err_code, $e))->setMethod($method)->setEndpoint($endpoint);
		} catch ( \Exception $e ) {
			// その他
			list($err_code, $err_msg) = config('integrated_api.errors.CONNECTION_UNKNOWN');
			throw (new IntegratedAPIConnectionException($err_msg, $err_code, $e))->setMethod($method)->setEndpoint($endpoint);
		}

		$this->log($method, $endpoint, $response);

		// 統合API専用のレスポンスクラス生成
		$IntegratedAPIResponse = new IntegratedAPIResponse($response, $method, $data);
		// HTTPステータスコードが異常系の場合は例外を投げる
		$IntegratedAPIResponse->throwIfInvalidHttpStatusCode();

		return $IntegratedAPIResponse;
	}

	/*
		エンドポイントの情報を取得
		@throws InvalidArgumentException
		@param string $key
		@return Array
	*/
	protected function getEndpointByKey($key) {
		$endpoint = config("integrated_api.endpoints.{$key}") ?? null;

		if ( empty($endpoint) ) throw new \InvalidArgumentException("Endpoint key {$key} is not defined.");

		return $endpoint;
	}

	/*
		API接続用クライアント生成
		@return PendingRequest
	*/
	protected function generateClient() {
		$PendingRequest = new PendingRequest();

		// BASIC認証情報付与
		$this->addBasicAuthentication($PendingRequest);

		return $PendingRequest;
	}

	/*
		BASIC認証情報付与
		@return void
	*/
	protected function addBasicAuthentication(PendingRequest $PendingRequest) {
		$basic_auth = config('integrated_api.env.BASIC_AUTHENTICATION');

		// ID・パスワードがともに設定されている場合のみ作用
		if ( isset($basic_auth['ID']) && isset($basic_auth) ) {
			$PendingRequest->withBasicAuth($basic_auth['ID'], $basic_auth['PASSWORD']);
		}
	}

	/*
		HTTPメソッド取得
		@param string $method
		@return string
	*/
	protected function convertMethod($method) {
		$method = Str::lower($method);

		switch($method) {
			case('get'):
			case('post'):
			case('put'):
			case('delete'):
				break;
			default:
				// 上記以外は全てgetとして取り扱う（暫定）
				$method = 'get';
				break;
		}

		return $method;
	}

	/*
		送信先URL生成
		@throws RuntimeException
		@param string $path
		@return string
	*/
	protected function generateAbsEndpoint($path) {
		$url = config('integrated_api.env.URL');

		if ( !isset($url) ) throw new \RuntimeException("Integrated API URL is not defined.");

		return "{$url}/{$path}";
	}

	/*
		システムキー取得
		@throws RuntimeException
		@return string
	*/
	protected function getSystemKey() {
		$system_key = config('integrated_api.env.SYSTEM_KEY');

		if ( !isset($system_key) ) throw new \RuntimeException("System key is not defined.");

		return $system_key;
	}

	/*
		ログ出力
		@params string $method
		@params string $endpoint
		@params Response $Response
		@return void
	*/
	public function log($method, $endpoint, Response $Response) {
		if ( empty(config('integrated_api.env.RESPONSE_LOG')) ) return;

		logger()->info("{$method} {$endpoint} {$Response->status()}");
	}
}