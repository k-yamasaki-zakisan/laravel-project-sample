<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Extensions\ValidationRules; // 追記

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // バリデーションルールの拡張
        foreach( app(ValidationRules::class)->getRules() as $rule => list($method, $message) ){
            \Validator::extend($rule, $method, $message);
        }
    }
}