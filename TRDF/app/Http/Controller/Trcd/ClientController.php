<?php

namespace App\Http\Controllers;

use DB;
use App\Client;
use App\ClientUser;
use App\Prefecture;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

use App\Repositories\ClientRepositoryInterface AS ClientRepository;

use Kris\LaravelFormBuilder\FormBuilderTrait;
use Kris\LaravelFormBuilder\FormBuilder;

use App\Forms\ClientForm;

use App\Http\Requests\ClientCreatePostRequest;

use App\Services\EnvService;
use App\Services\ClientService;
use App\Services\ClientSettingService;
use App\Services\ClientUserService;
use App\Services\QuoteStatusService;
use App\Services\PasswordService;
use App\Services\Trcd\TrcdService;
use App\Services\ClientEmployeeService;

class ClientController extends Controller {

	use FormBuilderTrait;

	/**
	 * 一覧ページ
	 */
	public function index() {
		//$clients = Client::with('prefecture')->get();
		$clients = Client::orderBy('id', 'DESC')->paginate(20);
		return view('clients.index', compact('clients'));
	}

	/**
	 * 編集フォーム
	 */
	public function edit(
		$id,
		FormBuilder $formBuilder,
		ClientRepository $objClientRepo
	) {
		#レコードをidで指定
		$client = $objClientRepo->findOrFail($id);
		$form = $formBuilder->create(ClientForm::class, [
			'method' => 'PUT',
			'url' => route('clients.update', ['id'=>$client->id]),
			'model' => $client
		]);

		return view('clients.edit',compact('client','form'));
	}

	/**
	 * 更新保存処理
	 */
	public function update(
		Request $request,
		ClientRepository $objClientRepo,
		ClientService $objClientService
	) {
		$id = $request->id;
		$client = $objClientRepo->findOrFail($id);

		$form = $this->form(ClientForm::class, [
			'model' => $client,
		]);

		//フォームバリデーション
		if ( !$form->isValid() ) return redirect()->back()->withErrors($form->getErrors())->withInput();

		//保存データ整形
		$values = array_only($form->getFieldValues(), [ 'name', 'phonetic', 'email', 'tel' ]);
		$values['id'] = $client->id;

		//認証キーが設定されていない場合は挿入
		if ( !isset($client->auth_key) ) $values['auth_key'] = $objClientService->generateAuthKey();
		
		try {
			$result = $objClientService->save($values);

			if ( empty($result) ) throw new \Exception("DB:保存時エラー");
		} catch (\Exception $e) {
			logger()->error($e->getMessage());

			$validator = $objClientService->getLastValidator();
			$errors['db'] = empty($validator) ? [] : join("\n", Arr::flatten($validator->errors()->toArray()));
			logger()->error($errors);

			$request->session()->flash('error_message', '保存に失敗しました。');

			return redirect()->back()->withErrors($errors)->withInput();
		}

		$request->session()->flash('success_message', '保存が完了しました。');

		return redirect(route('clients.index'));
	}


	/**
	 * 登録フォーム
	 */
	public function create(Request $request, FormBuilder $formBuilder) {
		$form = $formBuilder->create(ClientForm::class, [
			'method' => 'POST',
			'url' => route('clients.store')
		]);
		return view('clients.create', compact('form'));
	}

	/**
	 * 削除処理
	 */
	// deleteの処理は論理削除で！
	public function delete(Request $request, $id) {
		Client::find($id)->delete(); // softDelete
		$request->session()->flash('success_message', '削除が完了しました。');
		return redirect(route('clients.index'));
	}

	/*
		保存処理
	*/
	public function store(ClientCreatePostRequest $request,
		ClientService $objClientService,
		EnvService $objEnvService,
		ClientSettingService $objClientSettingService,
		QuoteStatusService $objQuoteStatusService,
		ClientUserService $objClientUserService,
		TrcdService $objTrcdService,
		ClientEmployeeService $objClientEmployeeService
	) {

		logger(__METHOD__);
		$post_data = array_only($request->all(), ['name', 'phonetic', 'email', 'tel']);
		logger($post_data);
		$validator = null;

		try {
			$result = $objClientService->createViaWeb($post_data,
				$objEnvService,
				$objClientSettingService,
				$objQuoteStatusService,
				$objClientUserService,
				$objTrcdService,
				$objClientEmployeeService
			);
			logger($result);

			if ( !$result ) {
				$validator = $objClientService->getLastValidator();
				throw new \Exception('保存に失敗しました。');
			}
		} catch(\Exception $e) {
			Log::error($e->getMessage());
			$errors = ['db' => $e->getMessage()];

			if ( !empty($validator) ) $errors += $validator->errors()->toArray();

			return redirect()->back()
				->withInput($request->input())
				->withErrors($errors);
		}

		$request->session()->flash('success_message', '保存が完了しました。');
		return redirect(route('clients.index'));
	}

	/*
		指定企業社員の引出し済み金額を更新
	*/
	public function update_withdraw_amount_already_this_month(
		Request $request,
		TrcdService $objTrcdService,
		$client_id
	) {
		$result = $objTrcdService->UpdateWithdrawAmountAlreadyThisMonthByClientId($client_id);

		if ( $result) {
			$request->session()->flash('success_message', '払出し済み金額の更新が完了しました。');
			return redirect(route('clients.index'));
		} else {
			$request->session()->flash('error_message', '払出し済み金額の更新に失敗しました。');
			return back();
		}
	}

/*****
	public function store(ClientCreatePostRequest $request, ClientService $objClientService, ClientUserService $objClientUserService) {
		$post_data = array_only($request->all(), ['name', 'phonetic', 'email', 'tel', 'password']);
		$client_save_data = array_only($post_data, ['name', 'phonetic', 'email', 'tel']);
		$validator = null;

		DB::beginTransaction();

		try{
			$client = $objClientService->create($client_save_data);

			if ( !$client ) {
				$validator = $objClientService->getLastValidator();
				throw new \Exception('クライントの保存に失敗しました。');
			}

			//管理者アカウントも同時に作成 現状はadmin:passwordで作成
			$client_user_save_data = array_only($post_data, ['name', 'email']);
			$client_user_save_data += [
				'client_id' => $client['id'],
				'login_name' => 'admin',
				'password' => PasswordService::encryptPassword('password'),
			];

			$client_user = $objClientUserService->create($client_user_save_data);

			if ( !$client_user ) {
				$validator = $objClientUserService->getLastValidator();
				throw new \Exception('クライントユーザーの保存に失敗しました。');
			}
		}catch(\Exception $e){
			DB::rollBack();
			Log::error($e);

			$errors = ['db' => $e->getMessage()];
			if ( !empty($validator) ) $errors += $validator->errors()->toArray();

			return redirect()->back()
				->withInput($request->input())
				->withErrors($errors);
		}

		DB::commit();
		$request->session()->put('client', $client);
		$request->session()->put('client_user', $client_user);

		return redirect(route('clients.success'));
	}


	// 完了画面
	function success(Request $request){
		//登録時に保持した情報を開放
		$client = $request->session()->get('client');
		$client_user = $request->session()->get('client_user');
		return view("clients.success", compact(["client", "client_user"]));
	}

	# 登録フォーム
	public function index(){
		$client_code = $this->random_code();
		return view('client_create', compact("client_code"));
	}

	public function create(Request $request){
		$client = new Client;
		$client_user = new ClientUser;
		$client_code = $_POST["client_code"];


		# トランザクション
		DB::beginTransaction();

		try {
			# Clientの登録
			$client->login_code = $request->input("client_code");
			$client->name = $request->input("name");
			$client->phonetic = $request->input("phonetic");
			$client->email = $request->input("email");
			$client->tel = $request->input("tel");

			# Clientの登録
			if($client->save()){
				# Clientセッション保持
				$request->session()->put('client', $client);


				# ClientUserの登録
				$client_user->client_id = $client->id;
				$client_user->login_name = "admin";
				$client_user->email = $client->email;
				$client_user->password = encrypt($request->input("password")); # パスワードの暗号化
				$client_user->name = $client->name;

				# ClientUserの登録
				if($client_user->save()){
					# ClientUserセッション保持
					$request->session()->put('client_user', $client_user);

					DB::commit();
					return redirect("/client_create/success");
				}else{
					throw new \Exception;
				}
			}else{
				throw new \Exception;
			}
		} catch (\Exception $e){
			DB::rollBack();
			return redirect()->back()->with("client_code");
		}
	}

	function random_code($length=9){
		$chars = '0123456789';
		$count = mb_strlen($chars);
			for ($i = 0, $result = ''; $i < $length; $i++) {
			$index = rand(0, $count - 1);
			$result .= mb_substr($chars, $index, 1);
		}

		return strval($result);
	}
*****/
}
?>
