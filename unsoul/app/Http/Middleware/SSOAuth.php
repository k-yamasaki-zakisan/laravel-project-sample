<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Aacotroneo\Saml2\Saml2Auth;

class SSOAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 未ログイン時
        if ( Auth::guest() ) {
            if ($request->ajax()) {
                return response('Unauthorized.[AJAX Request]', 401);
            } else {
                // SAML認証ログイン画面へ
                $saml2Auth = new Saml2Auth(Saml2Auth::loadOneLoginAuthFromIpdConfig('doraever'));
                return $saml2Auth->login(route('unsoul.login.select'));
            }
        }

        return $next($request);
    }
}