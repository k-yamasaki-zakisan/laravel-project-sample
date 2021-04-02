<?php

namespace App\Http\Controllers;

// Services
use App\Services\EmployeeService;
// Requests
use App\Http\Requests\Employees\SearchEmployeeRequest;
use App\Http\Requests\Employees\RegisterEmployeeConfirmationRequest;
use App\Http\Requests\Employees\UpdateEmployeeRequest;
use Illuminate\Http\Request;
// Usecases
use App\Usecases\Employees\SearchEmployeeUsecase;
use App\Usecases\Employees\DeleteEmployeeUsecase;
use App\Usecases\Employees\RegisterEmployeeUsecase;
use App\Usecases\Employees\StoreEmployeeUsecase;
use App\Usecases\Employees\EditEmployeeUsecase;
use App\Usecases\Employees\UpdateEmployeeUsecase;
// Validate
use Illuminate\Support\Facades\Validator;

class EmployeeController extends UnsoulBaseController
{

    protected $EmployeeService;
	protected $session_keys;

    public function __construct(
            EmployeeService $EmployeeService
    ) {
        $this->EmployeeService = $EmployeeService;
        // Sessionキー
        $this->session_keys = [
            'REGISTRATION' => config('unsoul.session_keys.EMPLOYEE_REGISTRATION'),
        ];
    }


    public function index(SearchEmployeeRequest $request, SearchEmployeeUsecase $usecase){
        $data = $usecase($request);

	return view('unsoul.employees.index', $data);
    }

    /*
        登録画面
    */
	public function register(Request $request, RegisterEmployeeUsecase $usecase) {
		$previous_url = url()->previous();
		$urls = [
			'index' => route('unsoul.employees.index'),
			'confirm' => route('unsoul.employees.register.confirm'),
			'store' => route('unsoul.employees.store'),
		];

		if ( $this->isSameUrlBetween($previous_url, $urls['index']) ) {
			// 遷移元が法人一覧の場合はクエリパラメータを保持して設定
			session()->put("{$this->session_keys['REGISTRATION']}.back_to", $previous_url);
		} else if (
			$this->isSameUrlBetween($previous_url, $urls['confirm'])
			|| $this->isSameUrlBetween($previous_url, $urls['store'])
		) {
			// 遷移元が確認画面・登録処理の場合
			// _old_inputが未設定の場合は入力値復元
			if ( !session()->has('_old_input') ) session()->flash('_old_input', session("{$this->session_keys['REGISTRATION']}.data"));
		}

		$data = $usecase();
		$data['back_to'] = session("{$this->session_keys['REGISTRATION']}.back_to") ?? $urls['index'];
//dump(session()->all());

		return view('unsoul.employees.register', $data);
	}

    /*
        登録確認画面
    */
	public function confirmRegister(RegisterEmployeeConfirmationRequest $request, RegisterEmployeeUsecase $usecase) {
		$data = $usecase();
        	// ToDo:微妙。リクエスト過多になりかねない
        	$request->validateInRules(
			$data['genders']->keys()->toArray(),
			$data['employment_statuses']->keys()->toArray(),
			$data['prefectures']->keys()->toArray()
        	);

		$data['validated'] = $request->validated();
		session()->put("{$this->session_keys['REGISTRATION']}.data", $data['validated']);

		return view('unsoul.employees.register_confirm', $data);
	}

    /*
        登録処理
    */
    public function store(Request $request, StoreEmployeeUsecase $usecase) {

		$request_data = session("{$this->session_keys['REGISTRATION']}.data");
		$ErrorResponse = redirect()->route('unsoul.employees.register');

		if ( empty($request_data) ) {
			return $ErrorResponse->with('error_message', config('unsoul.messages.SESSION_DATA_NOT_FOUND'));
		}

		try {
			$data = $usecase($request_data);

			if ( empty($data) ) throw new \RuntimeException("Failed to save Employee.");

		} catch( \Exception $e ) {
			logger("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
			$ErrorResponse->with('error_message', config('unsoul.messages.SAVE_NG'));

			if ( $e instanceof IntegratedAPIValidationException ) {
				$ErrorResponse->with('errors', $this->createMessageBag($e->getErrors()));
			}

			return $ErrorResponse;
		}

		// セッションデータ削除
		session()->forget("{$this->session_keys['REGISTRATION']}");

		return redirect()->route('unsoul.employees.index')->with('success_message', '');
    }

    /*
        削除
    */
    public function delete(DeleteEmployeeUsecase $usecase, $link_key) {
        try {
            $result = $usecase($link_key);

            if ( empty($result) ) throw new \Exception("Failed to delete employee.[{$link_key}]");

            session()->flash('flash_message', config('unsoul.messages.DELETE_OK'));
        } catch( \Exception $e ) {
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            session()->flash('flash_message', config('unsoul.messages.DELETE_NG'));
        }

        return redirect()->route('unsoul.employees.index');
    }

	/*
        	編集ページ
    	*/
	public function edit(EditEmployeeUsecase $usecase, string $link_key) {
		try {
			$data = $usecase($link_key);
		} catch( \Exception $e ) {
            		logger($e->getMessage());
        		return redirect()->route('unsoul.employees.index');
		}

		return view('unsoul.employees.edit', $data);

	}

	/*
                更新
        */
	public function update(UpdateEmployeeRequest $request, UpdateEmployeeUsecase $usecase, $link_key) {
		$validated = $request->validated();
		$validated['link_key'] = $link_key;

		$ErrorResponse = redirect()->route('unsoul.employees.edit', $link_key);

		try {
			$reselt = $usecase($validated);

			if ( empty($reselt) ) throw new \RuntimeException("Failed to update Employee.");
		} catch( \Exception $e ) {
			logger($e->getMessage());

			return $ErrorResponse;
		}

		return redirect()->route('unsoul.employees.index');
	}
}