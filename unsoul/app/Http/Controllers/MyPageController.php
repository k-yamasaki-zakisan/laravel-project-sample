<?php

namespace App\Http\Controllers;

// Services
use App\Services\MypageService;
// Usecases
// use App\Usecases\Mypages\CreatePersonUsecase;

use Illuminate\Http\Request;
// Requests
use App\Http\Requests\MyPage\UpdatePersonRequest;
use App\Http\Requests\MyPage\StoreOfficeRequest;
use App\Http\Requests\MyPage\UpdateOfficeRequest;
use App\Http\Requests\MyPage\UpdateCorporationRequest;
// Usecases
use App\Usecases\MyPages\EditPersonUsecase;
use App\Usecases\MyPages\UpdatePersonUsecase;
use App\Usecases\MyPages\CreateOfficeUsecase;
use App\Usecases\MyPages\StoreOfficeUsecase;
use App\Usecases\MyPages\EditOfficeUsecase;
use App\Usecases\MyPages\UpdateOfficeUsecase;
use App\Usecases\MyPages\EditCorporationUsecase;
use App\Usecases\MyPages\UpdateCorporationUsecase;
use App\Usecases\MyPages\DeleteOfficeUsecase;

class MyPageController extends UnsoulBaseController
{
	public function index(Request $request) {
		return view('unsoul.mypage.index');
	}

	public function edit_self(EditPersonUsecase $usecase) {
		$login_person_id = auth()->user()->person['person_id'];
		$login_corporation_id = auth()->user()->corporation['corporation_id'];

		$data = $usecase($login_person_id, $login_corporation_id);

		return view('unsoul.mypage.persons.edit', $data);
	}

	public function update_self(UpdatePersonRequest $request, UpdatePersonUsecase $usecase) {
		$request_data = $request->validated();

		$login_person = auth()->user()->person;

		$request_data['person_id'] = $login_person['person_id'];
		$request_data['updated_by'] = $login_person['link_key'];
		$ErrorResponse = redirect()->route('unsoul.mypage.edit_self');

		try {
			$data = $usecase($request_data);

			if ( empty($data) ) throw new \RuntimeException("Failed to update Person.");

		} catch( \Exception $e ) {
			logger($e->getMessage());
			$ErrorResponse->with('error_message', config('unsoul.messages.SAVE_NG'));

			if ( $e instanceof IntegratedAPIValidationException ) {
				$ErrorResponse->with('errors', $this->createMessageBag($e->getErrors()));
			}

			return $ErrorResponse;
		}

		return redirect()->route('unsoul.mypage.edit_self')->with('success_message', config('unsoul.messages.SAVE_OK'));
	}

	public function edit_corporation(EditCorporationUsecase $usecase) {
		$login_corporation_id = auth()->user()->corporation['corporation_id'];
		//本社、都道府県リスト、事務所リスト取得
		$data = $usecase($login_corporation_id);

		return view('unsoul.mypage.corporations.edit', $data);
	}

	public function register_office(Request $request, CreateOfficeUsecase $usecase) {
		$login_corporation_id = auth()->user()->corporation['corporation_id'];
		//都道府県リスト作成
		$data = $usecase($login_corporation_id);
		$data['form_action'] = route('unsoul.mypage.offices.store');
		$data['is_register'] = true;

		return view('unsoul.mypage.offices.form_contents', $data);
	}

	public function edit_office (string $office_link_key, EditOfficeUsecase $usecase) {
		$login_corporation_id = auth()->user()->corporation['corporation_id'];

		$ErrorResponse = redirect()->route('unsoul.mypage.edit_self');

		try {
			$data = $usecase($office_link_key, $login_corporation_id);
		} catch ( \Exception $e ) {
                        logger($e->getMessage());

			return $ErrorResponse;
		}

		$data['form_action'] = route('unsoul.mypage.offices.update', $data['link_key']);
		$data['is_edit'] = true;

		return view('unsoul.mypage.offices.form_contents', $data);
	}

	public function store_office(StoreOfficeRequest $request, StoreOfficeUsecase $usecase) {
		$request_data = $request->validated();

		$request_data['corporation_id'] = auth()->user()->corporation['corporation_id'];
		$request_data['updated_by'] = auth()->user()->person['link_key'];
		$ErrorResponse = redirect()->route('unsoul.mypage.offices.register');

		try {
			$data = $usecase($request_data);

                        if ( empty($data) ) throw new \RuntimeException("Failed to create Office.");
		} catch ( \Exception $e ) {
			logger($e->getMessage());
                        $ErrorResponse->with('error_message', config('unsoul.messages.SAVE_NG'));

                        if ( $e instanceof IntegratedAPIValidationException ) {
                                $ErrorResponse->with('errors', $this->createMessageBag($e->getErrors()));
                        }

                        return $ErrorResponse;
		}

		return redirect()->route('unsoul.home')->with('success_message', config('unsoul.messages.SAVE_OK'));
	}

	public function update_office(string $office_link_key, UpdateOfficeRequest $request, UpdateOfficeUsecase $usecase) {
		$request_data = $request->validated();
		//仮実装のためcorporation_idを77に固定
        $request_data['link_key'] = $office_link_key;
		$request_data['updated_by'] = auth()->user()->person['link_key'];

		//更新ページのパラメータがとってこれないため暫定的に事務所の新規登録ページに遷移
		$ErrorResponse = redirect()->route('unsoul.mypage.offices.edit', $office_link_key);

		try {
			$data = $usecase($request_data);

			if ( empty($data) ) throw new \RuntimeException("Failed to update Office..");
		} catch ( \Exception $e ) {
                        logger($e->getMessage());
                        $ErrorResponse->with('error_message', config('unsoul.messages.SAVE_NG'));

                        if ( $e instanceof IntegratedAPIValidationException ) {
                                $ErrorResponse->with('errors', $this->createMessageBag($e->getErrors()));
			}
			return $ErrorResponse;
		}

		return redirect()->route('unsoul.home')->with('success_message', config('unsoul.messages.SAVE_OK'));
	}

	public function update_corporation(UpdateCorporationRequest $request, UpdateCorporationUsecase $usecase) {
		$request_data = $request->validated();

		$request_data['corporation_id'] = auth()->user()->corporation['corporation_id'];
		$request_data['updated_by'] = auth()->user()->person['link_key'];
		$ErrorResponse = redirect()->route('unsoul.mypage.corporations.edit');

		try {
                        $data = $usecase($request_data);

                        if ( empty($data) ) throw new \RuntimeException("Failed to update corporation..");
                } catch ( \Exception $e ) {
                        logger($e->getMessage());
                        $ErrorResponse->with('error_message', config('unsoul.messages.SAVE_NG'));

                        if ( $e instanceof IntegratedAPIValidationException ) {
                                $ErrorResponse->with('errors', $this->createMessageBag($e->getErrors()));
                        }
                        return $ErrorResponse;
                }

                return redirect()->route('unsoul.home')->with('success_message', config('unsoul.messages.SAVE_OK'));
	}

	public function delete_office(string $office_link_key, DeleteOfficeUsecase $usecase) {
		$request_data = [
			'link_key' => $office_link_key,
			'updated_by' => auth()->user()->person['link_key'],
		];

		$ErrorResponse = redirect()->route('unsoul.mypage.offices.edit', $office_link_key);

		try {
			$result = $usecase($request_data);

			if ( empty($result) ) throw new \RuntimeException("Failed to delete office..");
		} catch ( \Exception $e ) {
                        logger($e->getMessage());
                        //$ErrorResponse->with('error_message', config('unsoul.messages.'));

                        return $ErrorResponse;

		}

		return redirect()->route('unsoul.home');
	}

}