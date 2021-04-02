<?php

namespace App\Http\Controllers;

// Services
use App\Services\LicenseService;
// Requests
use App\Http\Requests\Licenses\SearchLicenseRequest;
use App\Http\Requests\Licenses\RegisterLicenseRequest;
use App\Http\Requests\Licenses\StorePersonLicenseRequest;
use App\Http\Requests\Licenses\UpdatePersonLicenseRequest;
use App\Http\Requests\Licenses\SavePersonFreeLicenseRequest;
// Usecases
use App\Usecases\Licenses\SearchLicenseUsecase;
use App\Usecases\Licenses\EditLicenseUsecase;
use App\Usecases\Licenses\RegisterLicenseUsecase;
use App\Usecases\Licenses\StorePersonLicenseUsecase;
use App\Usecases\Licenses\DeletePersonLicenseUsecase;
use App\Usecases\Licenses\UpdatePersonLicenseUsecase;
use App\Usecases\Licenses\RegisterFreeLicenseUsecase;
use App\Usecases\Licenses\SavePersonFreeLicenseUsecase;

class LicenseController extends UnsoulBaseController
{

	protected $LicenseService;

    public function __construct(
            LicenseService $LicenseService
    ) {
            $this->LicenseService = $LicenseService;
    }

    /*
        資格管理画面
    */
    public function index(SearchLicenseRequest $request, SearchLicenseUsecase $usecase, $person_id) {
        $data = $usecase($request,$person_id);

        if ( empty($data) ) abort(404);

        return view('unsoul.licenses.index', $data);
    }

    /*
        追加画面
    */
    public function register(RegisterLicenseRequest $request, RegisterLicenseUsecase $usecase, $person_id) {
        $choice_license_category_id = $request->validated();

        $data = $usecase($choice_license_category_id);
        //$data += ['person_id' => auth()->user()->person['person_id']];
        $data += ['person_id' => $person_id];

        return view('unsoul.licenses.register', $data);
    }

    /*
        追加処理
    */
	public function store_person_license(StorePersonLicenseRequest $request, StorePersonLicenseUsecase $usecase, $person_id) {
		$request_data = $request->validated();
		$request_data['updated_by'] = auth()->user()->person['link_key'];
		//$request_data['person_id'] = auth()->user()->person['person_id'];
		$request_data['person_id'] = $person_id;

		$ErrorResponse = redirect()->route('unsoul.licenses.register', $person_id);

		try {
			$data = $usecase($request_data);

			if ( empty($data) ) throw new \RuntimeException("Failed to save person_license..");
		} catch ( \Exception $e ) {
			logger($e->getMessage());
            $ErrorResponse->with('error_message', config('unsoul.messages.SAVE_NG'));

			return $ErrorResponse;
		}

		return redirect()->route('unsoul.person_licenses.index', $person_id)->with('success_message', config('unsoul.messages.SAVE_OK'));
	}

    /*
        フリー入力画面
    */
    public function register_free_license(RegisterFreeLicenseUsecase $usecase, $person_id) {
        $data = $usecase();
        //$data += ['person_id' => auth()->user()->person['person_id']];
        $data += ['person_id' => $person_id];

        return view('unsoul.licenses.register_free', $data);
    }

	/*
		フリー資格登録更新用のメソッド
	*/
	public function save_person_free_license(SavePersonFreeLicenseRequest $request, SavePersonFreeLicenseUsecase $usecase, $person_id) {
		$request_data = $request->validated();
		$request_data['updated_by'] = auth()->user()->person['link_key'];
		$request_data['person_id'] = $person_id;

		$ErrorResponse = redirect()->route('unsoul.licenses.register_free_license', $person_id);

		try {
			$data = $usecase($request_data);

                        if ( empty($data) ) throw new \RuntimeException("Failed to save person_free_license..");
		} catch ( \Exception $e ) {
                        logger($e->getMessage());
                        $ErrorResponse->with('error_message', config('unsoul.messages.SAVE_NG'));

                        return $ErrorResponse;
                }

		return redirect()->route('unsoul.person_licenses.index', $person_id);
	}

    /*
        編集画面
    */
	public function edit(SearchLicenseRequest $request, EditLicenseUsecase $usecase, $person_id, $license_id) {
		// 空配列の作成
		$license = [];

		$license['person_id'] = $person_id;
		$license['license_id'] = $license_id;

		$licenses = $usecase($request, $license);

		return view('unsoul.licenses.edit', compact(
            'licenses',
			'person_id',
			'license_id',
        ));

    }

    /*
        更新処理
    */
	public function update_person_license(UpdatePersonLicenseRequest $request, UpdatePersonLicenseUsecase $usecase, $person_id, string $person_license_link_key) {
		$request_data = $request->validated();
		$request_data['updated_by'] = auth()->user()->person['link_key'];
        $request_data['link_key'] = $person_license_link_key;

		$ErrorResponse = redirect()->route('unsoul.licenses.register', $person_id);

		try {
                        $data = $usecase($request_data);

                        if ( empty($data) ) throw new \RuntimeException("Failed to update person_license..");
                } catch ( \Exception $e ) {
                        logger($e->getMessage());
                        //$ErrorResponse->with('error_message', config('unsoul.messages.SAVE_NG'));

                        return $ErrorResponse;
                }

                return redirect()->route('unsoul.person_licenses.index', $person_id);
	}

    /*
        削除
    */
	public function delete_person_license(DeletePersonLicenseUsecase $usecase, $person_id, string $person_license_link_key) {
		$request_data = [
			'updated_by' => auth()->user()->person['link_key'],
			'link_key' => $person_license_link_key,
		];

		$ErrorResponse = redirect()->route('unsoul.person_licenses.index', $person_id);

		try {
			$data = $usecase($request_data);

                        if ( empty($data) ) throw new \RuntimeException("Failed to delete person_license..");
		} catch ( \Exception $e ) {
                        logger($e->getMessage());
                        $ErrorResponse->with('error_message', config('unsoul.messages.SAVE_NG'));

                        return $ErrorResponse;
                }

                return redirect()->route('unsoul.person_licenses.index', $person_id);
	}

}