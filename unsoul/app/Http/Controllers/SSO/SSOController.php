<?php
namespace App\Http\Controllers\SSO;

use Aacotroneo\Saml2\Http\Controllers\Saml2Controller;
// Requests
use App\Http\Requests\SSO\SetLoginUserPostRequest;
// Usecases
use App\Usecases\SSO\SelectLoginUserUsecase;
use App\Usecases\SSO\SetLoginUserUsecase;
// Login用
use App\Auth\LoginUser;

class SSOController extends Saml2Controller {
	protected $session_keys;

	public function __construct() {
		// Sessionキー
		$this->session_keys = [
			'SAML_ATTRIBUTES' => config('unsoul.session_keys.SAML_ATTRIBUTES'),
		];
    }

/*
    public function login(\Aacotroneo\Saml2\Saml2Auth $saml2Auth) {
logger(__METHOD__);
        $loginRedirect = route('unsoul.login');
        //$this->saml2Auth->login($loginRedirect);
        $saml2Auth->login($loginRedirect);
    }
*/

/*
    public function logout(\Aacotroneo\Saml2\Saml2Auth $saml2Auth, \Illuminate\Http\Request $request) {
        \Illuminate\Support\Facades\Auth::logout();
        //auth()->logout();
        parent::logout($saml2Auth, $request);
    }
*/

    /*
        ログインユーザー選択画面
    */
    public function selectLoginUser(SelectLoginUserUsecase $usecase) {
        $session_key = $this->session_keys['SAML_ATTRIBUTES'];

        // 認証済みの場合はリダイレクト
        if ( !empty(auth()->user()) ) {
            session()->forget($session_key);
            return redirect()->route('unsoul.home');
        }

        // セッションに値が格納されていない場合は404
        if ( !session()->has($session_key) ) abort(404);

        $session_data = session($session_key);
        $employees = $usecase($session_data);

        // 社員として登録されていない場合
        if ( empty($employees) ) return view('unsoul.sso.no_employees');

        // 単一企業に所属している場合は自動ログイン
        if ( count($employees) === 1 ) {
            try {
                $this->_doLogin($employees[0]);
            } catch( \Exception $e ) {
                logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
                return redirect()->route('logout');
            }

            return redirect()->route('unsoul.home');
        }

        // 複数企業に所属している場合は選択画面
        $person_name = $session_data['givenName'];

        // セッション内の従業員ID配列キーにlink_keyを設定
        session()->put(
            "{$session_key}.employeeIds",
            collect($employees)->pluck('employee_id', 'link_key')->toArray()
        );

        return view('unsoul.sso.select_login_user', compact(
            'person_name' ,
            'employees'
        ));
    }

    /*
        ログインユーザー設定
    */
    public function setLoginUser(SetLoginUserPostRequest $request, SetLoginUserUsecase $usecase) {
        $session_key = $this->session_keys['SAML_ATTRIBUTES'];

        // 認証済みの場合はリダイレクト
        if ( !empty(auth()->user()) ) {
            session()->forget($session_key);
            return redirect()->route('unsoul.home');
        }

        // セッションに値が格納されていない場合は404
        if ( !session()->has($session_key) ) abort(404);

        $validated = $request->validated();
        $session_data = session($session_key);

        // バリデーション
        validator(
            $validated,
            [ '_k' => 'in:' . join(',', array_keys($session_data['employeeIds']))],
            [ '_k.in' => '不正な値が送信されました']
        )->validate();

        $employee_id = $session_data['employeeIds'][$validated['_k']];
        $employee = $usecase($employee_id);

        if ( empty($employee) ) {
            // 検索失敗
            logger()->error("Failed to set LoginUser, could not be identified.[employee_id:{$employee_id}]");
            return redirect()->route('unsoul.login.select');
        }

        $this->_doLogin($employee);

        return redirect()->route('unsoul.home');
    }

    /*
        認証処理
    */
    protected function _doLogin(Array $login_user_data) {
        $LoginUser = LoginUser::createNewInstance($login_user_data);
        auth()->login($LoginUser);
        session()->forget($this->session_keys['SAML_ATTRIBUTES']);
    }
}