<?php

namespace App\Http\Controllers;

// Services
use App\Services\CorporationService;
// Requests
use App\Http\Requests\Corporations\SearchCorporationRequest;
use App\Http\Requests\Corporations\StoreCorporationRequest;
use App\Http\Requests\Corporations\RegisterCorporationGetRequest;
use Illuminate\Http\Request;
// Usecases
use App\Usecases\Corporations\SearchCorporationUsecase;
use App\Usecases\Corporations\CreateCorporationUsecase;
use App\Usecases\Corporations\StoreCorporationUsecase;
// Exceptions
use App\Libraries\IntegratedAPI\Exceptions\IntegratedAPIValidationException;

class CorporationController extends UnsoulBaseController
{
	protected $CorporationService;
	protected $session_keys;

	public function __construct(
		CorporationService $CorporationService
	) {
		$this->CorporationService = $CorporationService;
		// Sessionキー
		$this->session_keys = [
			'REGISTRATION' => config('unsoul.session_keys.CORPORATION_REGISTRATION'),
		];
	}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SearchCorporationRequest $request, SearchCorporationUsecase $usecase)
    {
		$corporations = $usecase($request);
		$search = $request->search ?? [];

        return view('unsoul.corporations.index', compact(
			'search',
			'corporations'
		));
    }

    /**
     * 登録画面
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request, CreateCorporationUsecase $usecase)
    {
		$previous_url = url()->previous();
		$urls = [
			'index' => route('unsoul.corporations.index'),
			'confirm' => route('unsoul.corporations.confirm'),
			'store' => route('unsoul.corporations.store'),
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
		$data['form_action'] = $urls['confirm'];
		$data['back_to'] = session("{$this->session_keys['REGISTRATION']}.back_to") ?? $urls['index'];

        return view('unsoul.corporations.form_contents', $data);
    }

    /**
     * 確認画面
     *
     * @return \Illuminate\Http\Response
     */
    public function confirm(StoreCorporationRequest $request, CreateCorporationUsecase $usecase)
    {
		// sessionに格納
		$corporation = $request->validated();
		session()->put("{$this->session_keys['REGISTRATION']}.data", $corporation);

		$data = $usecase();
		$corporation_type = $data['corporation_types'][$corporation['corporation_type_id']] ?? null;

		// Viewのためのデータ補正
		if ( !empty($corporation_type) ) {
			if (! empty($corporation['corporation_pos']) ) $corporation['name'] .= "　{$corporation_type}";
			else $corporation['name'] = "{$corporation_type}　{$corporation['name']}";
		}
		$corporation['prefecture_name'] = $data['prefectures'][$corporation['prefecture_id']] ?? null;

        return view('unsoul.corporations.confirm', compact('corporation'));
    }

    /**
     * 登録
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, StoreCorporationUsecase $usecase)
    {
		$request_data = session("{$this->session_keys['REGISTRATION']}.data");
		$ErrorResponse = redirect()->route('unsoul.corporations.register');

		if ( empty($request_data) ) {
			return $ErrorResponse->with('error_message', config('unsoul.messages.SESSION_DATA_NOT_FOUND'));
		}

		try {
			$data = $usecase($request_data);

			if ( empty($data) ) throw new \RuntimeException("Failed to save Corporation.");

		} catch( \Exception $e ) {
			logger($e->getMessage());
			$ErrorResponse->with('error_message', config('unsoul.messages.SAVE_NG'));

			if ( $e instanceof IntegratedAPIValidationException ) {
				$ErrorResponse->with('errors', $this->createMessageBag($e->getErrors()));
			}

			return $ErrorResponse;
		}

		// セッションデータ削除
		session()->forget("{$this->session_keys['REGISTRATION']}");

		return redirect()->route('unsoul.corporations.index')->with('success_message', config('unsoul.messages.SAVE_OK'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
		$corporation = $this->CorporationService->findById($id);

		// 資本金桁補正
		if ( isset($corporation['capital']) ) {
			//$corporation['capital'] = intval($corporation['capital'] / 10000);
			$corporation['capital'] = $corporation['capital'] / 10000;
		}

        return view('unsoul.corporations.form', compact(
			'corporation'
		));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
		dd('仕様策定中のため未実装');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
		dd('仕様策定中のため未実装');
    }
}