<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
//use Throwable;
use Exception;
// Exceptions
use \Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
// Utilities
use Illuminate\Support\Arr;
use Carbon\Carbon;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request, Exception $exception) {
		// add by YuKaneko @2020.05.27
		// JSONでリクエストを受けた時に、JSONでエラーを返す
        $expects_json = $request->is('api/*') || $request->expectsJson();

        if ($expects_json) {
			// システム定義済みエラー
			$DEFINED_ERRORS = config('constants.errors');

			if ( $this->isHttpException($exception) ) {
				$status_code = $exception->getStatusCode();
				// ステータスが一致するシステム定義済みエラーを検索
				$filtered_error = Arr::first($DEFINED_ERRORS, function($value, $key) use($status_code) {
					return $value['status'] === $status_code;
				});

				if ( empty($filtered_error) ) {
					// 未定義エラーの場合
					return $this->_responseError($status_code, 'UNDEFINED', "未定義のエラーです。（status_code={$status_code}）");
				} else {
					// 定義済みエラーの場合
					return $this->_responseDefinedError($filtered_error);
				}
			} else {
				if ( $exception instanceof ValidationException ) {
					// バリデーションエラーの場合
					return $this->_responseDefinedError($DEFINED_ERRORS['VALIDATION_FAILED'], ['validation_errors' => $exception->errors()]);
				} else if ( $exception instanceof ModelNotFoundException ) {
					// findOrFail, firstOrFailの場合
					return $this->_responseDefinedError($DEFINED_ERRORS['NOT_FOUND']);
				} else {
					// それ以外は全て内部エラーとして（暫定的に）処理する
					return $this->_responseDefinedError($DEFINED_ERRORS['INTERNAL_SERVER_ERROR']);
				}
			}
		}

        return parent::render($request, $exception);
    }

	/*
		エラーレスポンスを返す
		@param int $error_status
		@param string $error_code
		@param string $error_message
		@param Array $extra_data 追加データ
		@return Response
	*/
	protected function _responseError($error_status, $error_code, $error_message, Array $extra_data = []) {
		$response_data = [
			'summary' => [
				'status' => 400,
				'error' => [
					'status' => $error_status,
					'code' => $error_code,
					'message' => $error_message,
					'occured_at' => now()->format('Y-m-d H:i:s'),
				]
			],
		];

		$response_data = array_merge($response_data, $extra_data);

		return response()->json($response_data, 200, [], JSON_UNESCAPED_UNICODE);
	}

	/*
		システム定義済みエラーレスポンスを返す
		@param Array $defined_error
		@param Array $extra_data 追加データ
		@return Response
	*/
	protected function _responseDefinedError(Array $defined_error, Array $extra_data = []) {
		return $this->_responseError($defined_error['status'], $defined_error['code'], $defined_error['message'], $extra_data);
	}
}