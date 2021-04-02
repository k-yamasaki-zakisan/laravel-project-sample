<?php

namespace App\Http\Controllers;

// Services
use App\Services\PersonService;
// Requests
use App\Http\Requests\Persons\SearchPersonRequest;
use App\Http\Requests\Persons\StorePersonRequest;
use App\Http\Requests\Persons\SearchCorporationRequest;
use App\Http\Requests\Persons\RegisterCorporationRequest;
use Illuminate\Http\Request;
// Usecases
use App\Usecases\Persons\CreatePersonUsecase;
use App\Usecases\Persons\StorePersonUsecase;

class PersonController extends UnsoulBaseController
{
	protected $PersonService;
	protected $per_page;
	protected $session_keys;

	public function __construct(
		PersonService $PersonService
	) {
		$this->PersonService = $PersonService;
		// Sessionキー
		$this->session_keys = [
			'REGISTRATION' => config('unsoul.session_keys.PERSON_REGISTRATION'),
		];
	}

	/**
     	*	（検索）一覧
	*/
	public function index(SearchPersonRequest $request) {
		//現在ページの取得と表示数の設定
		$req = $request->validated();
		$per_page = 50;
		$persons = $this->PersonService->searchPersons(
			$req,
			$per_page,
			$req['page'] ?? 1,
			['path' => $request->url()]
		);
		$search = $req['search'] ?? [];

		return view('unsoul.persons.index', compact(
			'search',
			'persons'
		));
	}

	/**
        *       登録
        */
	public function register(RegisterCorporationRequest $request, CreatePersonUsecase $usecase) {
		$previous_url = url()->previous();
		$urls = [
			'search_corporation' => route('unsoul.persons.register.search_corporation'),
			'confirm' => route('unsoul.persons.confirm'),
			'store' => route('unsoul.persons.store'),
		];

		$res = $request->validated();

		$data = $usecase();
		$data['form_action'] = route('unsoul.persons.confirm');
		$data['back_to'] = route('unsoul.persons.index');

		if ( $this->isSameUrlBetween($previous_url, $urls['search_corporation']) && $request->isMethod('POST') && !empty($res) ) {
			//postで選択企業がリクエストされた場合企業名をセッションに格納
			$data['corporation_name'] = $res['corporation_name'];
			session()->put("{$this->session_keys['REGISTRATION']}.data", $res);
		} else if (
			$this->isSameUrlBetween($previous_url, $urls['confirm'])
			|| $this->isSameUrlBetween($previous_url, $urls['store'])
		) {
			// 遷移元が確認画面・登録処理の場合
			// _old_inputが未設定の場合は入力値復元
			if ( !session()->has('_old_input') ) session()->flash('_old_input', session("{$this->session_keys['REGISTRATION']}.data"));
		}
		return view('unsoul.persons.form_contents', $data);
	}

	/**
        *       人登録用法人検索
        */
	public function search_corporation(SearchCorporationRequest $request) {
		$req = $request->validated();

		//初回入室時はリクエストがないため空リスト作成、同ページからリクエストがきた場合は企業リストを統合DBより持ってくる
		if ( !empty($req) ) {
			$per_page = 50;
			$corporations = $this->PersonService->searchCorporations(
				$req,
				$per_page,
				$req['page'] ?? 1,
				['path' => $request->url()]
                	);
		} else {
			$corporations = [];
		}

		$search = $req['search'] ?? [];

		return view('unsoul.persons.search_corporation', compact(
			'search',
                        'corporations'
		));
	}

	/**
        *       登録情報確認
        */
	public function confirm(StorePersonRequest $request, CreatePersonUsecase $usecase) {
		$corporation_info = session()->get("{$this->session_keys['REGISTRATION']}.data");
		$genders = $usecase();

		$res = $request->validated();

		//性別が有効であることを確認、有効でないなら性別nullにする
		if ( isset($res['gender_id']) && !empty($genders['genders'][$res['gender_id']]) ) {
			$gender_name = $genders['genders'][$res['gender_id']];
		} else {
			$gender_name = Null;
			$res['gender_id'] = Null;
		}

		$res = $res+$corporation_info;
		session()->put("{$this->session_keys['REGISTRATION']}.data", $res);

		$data = [
            'form_action' => route('unsoul.persons.store'),
            'back_to' => route('unsoul.persons.register'),
			'login_id' => $res['login_id'],
			'password' => $res['password'],
			'last_name' => $res['last_name'],
			'first_name' => $res['first_name'],
			'last_name_kana' => $res['last_name_kana'],
			'first_name_kana' => $res['first_name_kana'],
			'gender_name' => $gender_name,
			'birthday' => $res['birthday']  ?? Null,
			'corporation_name' => $res['corporation_name'],
			'is_confirm' => true,
                ];

		return view('unsoul.persons.form_contents', $data);
        }

	public function store(Request $request, StorePersonUsecase $usecase) {
		$request_data = session("{$this->session_keys['REGISTRATION']}.data");
		$ErrorResponse = redirect()->route('unsoul.persons.register');

		if ( empty($request_data) ) {
			return $ErrorResponse->with('error_message', config('unsoul.messages.SESSION_DATA_NOT_FOUND'));
		}

		try {
			$data = $usecase($request_data);

			if ( empty($data) ) throw new \RuntimeException("Failed to save Person.");

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

		return redirect()->route('unsoul.persons.index')->with('success_message', config('unsoul.messages.SAVE_OK'));
	}
}

trcd.aggregates.common.components.form.submit_button
