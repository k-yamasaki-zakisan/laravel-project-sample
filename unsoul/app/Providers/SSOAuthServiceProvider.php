<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Illuminate\Support\Facades\Auth;

class SSOAuthServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot() {
        parent::boot();

        Event::listen('Aacotroneo\Saml2\Events\Saml2LoginEvent', function (Saml2LoginEvent $event) {
            $messageId = $event->getSaml2Auth()->getLastMessageId();
            // Add your own code preventing reuse of a $messageId to stop replay attacks

            $user = $event->getSaml2User();
            $attrs = $user->getAttributes();

            //logger($user);
            //logger($user->getUserId());
            //logger($user->getAttributes());
            //logger($user->getRawSamlAssertion());

            $err_msg = null;

            if ( count($attrs['givenName']) !== 1 ) $err_msg = 'givenName count is invalid.';
            if ( count($attrs['eduPersonPrincipalName']) !== 1 ) $err_msg = 'eduPersonPrincipalName count is invalid.';
            if ( empty($attrs['employee_id']) ) $err_msg = 'employee ids is empty.';

            if ( !empty($err_msg) ) {
                logger()->error("{$err_msg}" . PHP_EOL . print_r($attrs, true));
                throw new \RuntimeException("Invalid SAML attributes, {$err_msg}");
            }

            session()->put(config('unsoul.session_keys.SAML_ATTRIBUTES'), [
                'eduPersonPrincipalName' => $attrs['eduPersonPrincipalName'][0],
                'givenName' => $attrs['givenName'][0],
                'employeeIds' => $attrs['employee_id'],
            ]);

            // 属性からLoginUserを生成
/*
            $LoginUser = new \App\Auth\LoginUser(
                $attrs['eduPersonPrincipalName'][0],
                $attrs['givenName'][0],
                $attrs['employee_id']
            );

            Auth::login($LoginUser);
*/
        });

        Event::listen('Aacotroneo\Saml2\Events\Saml2LogoutEvent', function ($event) {
            auth()->logout();
            //session()->save();
        });
    }
}