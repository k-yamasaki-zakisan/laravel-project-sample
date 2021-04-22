<?php

namespace App\Http\Controllers\Visitor;

//use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicExpositionBaseController;

//use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\HttpCommonLib;
use App\Exposition;

use App\Services\UserService;

use App\Exceptions\NonActiviExpositionByPreRegistUserException;
use App\Exceptions\CloseExpositionByPreRegistUserException;

use Carbon\Carbon;
use DateTime;

class VisitorBaseController extends PublicExpositionBaseController
{

    public function __construct()
    {
        $this->middleware('auth');

        //$this->beforeCheckSlug();
    }

    public function _checkCanView()
    {

        /*
		アクティブでなくても出展者アカウントはログインできる
		if( $this->_isActiveExpositionSlug() == false ){
			abort('404');
		}
		*/

        //if( $this->_isExhibitorUser() == false && $this->_isEntryUser() == false ){
        //	abort('404');
        //}

        //return true;

        if ($this->_isExhibitorUser()) return true;

        if ($this->_checkSessionTimeForEntryUser()) throw new CloseExpositionByPreRegistUserException("事前登録ユーザーが会期時間外にEXPOにログインしました");;

        if ($this->_isEntryUser()) return true;

        /*
		if( $this->_isEntryUserAtThisExposition() ) {
			throw new NonActiviExpositionByPreRegistUserException("事前登録ユーザーが非有効化しているEXPOにログインしました");
		}
        */

        abort('404');
    }
    public function _isActiveExpositionSlug()
    {
        $objExposition = HttpCommonLib::GetExposition(); //$this->_GetExposition();
        if ($objExposition == null || $objExposition->active_flag == false) {
            return false;
        }

        return true;
    }
    public function _isExhibitorUser()
    {
        $objExposition = HttpCommonLib::GetExposition(); //$this->_GetExposition();
        if ($objExposition == null) {
            abort('404');
        }

        return UserService::isExhibitorUserByExpoId(Auth::user()->id, $objExposition->id);
    }

    /**
     * ログインユーザーが有効化EXPOに事前登録済みかを確認する
     */
    public function _isEntryUser()
    {
        $expo_slug = HttpCommonLib::GetSlug(); //$this->_GetSlug();
        if (empty($expo_slug)) {
            return false;
        }

        return UserService::isEntryUserBySlug(Auth::user()->id, $expo_slug);
    }

    /**
     * ログインユーザーが事前登録済みかを確認する
     */
    public function _isEntryUserAtThisExposition()
    {
        $expo_slug = HttpCommonLib::GetSlug();
        if (empty($expo_slug)) {
            return false;
        }

        return UserService::isEntryUserBySlugWithAllStatusExposition(Auth::user()->id, $expo_slug);
    }

    private function _checkSessionTimeForEntryUser()
    {
        $expo_slug = HttpCommonLib::GetSlug();
        return UserService::checkSessionTime($expo_slug);
    }
}
