<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Auth::routes();
// authの設定
Auth::routes([
    'verify'   => true, // メール確認機能（※5.7系以上のみ）
    'register' => false, // デフォルトの登録機能OFF
    'reset' => true,  // メールリマインダー機能ON
]);


Route::get('/home', 'HomeController@index')->name('home');

// 一般ユーザー画面のルーティング
Route::prefix('expo/{expo_slug?}')->group(function () {
    // 事前登録ページ
    Route::get('/preregistration/entry/', 'UserPreRegistrationController@create')->name('user_pre_registration.create');
    Route::post('/preregistration/entry/', 'UserPreRegistrationController@store')->name('user_pre_registration.store');
    Route::get('/preregistration/complete/', 'UserPreRegistrationController@complete')->name('user_pre_registration.complete');


    // 下記、登録済みユーザー向けページ
    Route::get('/login/', 'Auth\LoginController@showLoginForm')->name('auth.login');
    Route::post('/login/', 'Auth\LoginController@login');
    Route::post('/logout/', 'Auth\LoginController@logout')->name('auth.logout');

    Route::middleware(['auth'])->group(function () {
        Route::namespace('Visitor')->name('visitor.')->group(function () {
            Route::get('/', 'HomeController@index')->name('home');
        });

        Route::namespace('ExhibitorAdmin')->prefix('admin')->name('exhibitor_admin.')->group(function () {
            Route::get('/', 'HomeController@index')->name('home');

            Route::get('/dashboard/', 'DashboardController@index')->name('dashboard');

            Route::get('/products/', 'ProductsController@index')->name('products.index');
            Route::get('/products/create/', 'ProductsController@create')->name('products.create');
            Route::get('/products/{id}/edit/', 'ProductsController@edit')->name('products.edit');
            Route::delete('/products/{id}/destroy', 'ProductsController@destroy')->name('products.destroy');

            Route::get('/videos/', 'VideosController@index')->name('videos.index');

            Route::get('/exhibitors/', 'ExhibitorsController@edit')->name('exhibitors.edit');
            Route::post('/exhibitors/update/', 'ExhibitorsController@update')->name('exhibitors.update');

            Route::get('/chat/', 'ChatController@index')->name('chat');
            Route::get('/inquiries/', 'InquiriesController@index')->name('inquiries');

            Route::get('/user_action_logs/timeline/', 'UserActionLogsController@timeline')->name('user_action_logs.timeline');
        });
    });
});

// 出展社管理画面のルーティング
#Route::namespace('ExhibitorAdmin')->prefix('exhibitor_admin')->middleware(['auth'])->name('exhibitor_admin.')->group(function(){
#	Route::get('/', 'HomeController@index')->name('home');
#});

#Route::namespace('ExhibitorAdmin')->prefix('exhibitor_admin')->name('exhibitor_admin.')->group(function(){
#	Route::middleware(['auth'])->group(function(){
#		//Route::get('/', 'HomeController@index')->name('home');
#	});
#});


// システム管理画面のルーティング
#Route::prefix('superadmin')->middleware(['auth:superadmin'])->group(function(){
#
#	Auth::routes([
#		'verify'   => true, // メール確認機能（※5.7系以上のみ）
#		//'register' => false, // デフォルトの登録機能OFF
#		'reset' => true,  // メールリマインダー機能ON
#	]);
#
#	Route::get('/', 'Superadmin\HomeController@index')->name('home');
#});


Route::namespace('Superadmin')->prefix('superadmin')->name('superadmin.')->group(function () {

    Auth::routes([
        'verify'   => true, // メール確認機能（※5.7系以上のみ）
        //'register' => false, // デフォルトの登録機能OFF
        'reset' => true,  // メールリマインダー機能ON
    ]);

    //Route::get('login', 'Auth\LoginController@login')->name('login');

    Route::middleware(['auth:superadmin'])->group(function () {
        Route::get('/', 'HomeController@index')->name('home');

        // Exposition Selector
        Route::post('/exposition_selector', 'ExpositionSelectorController@set')->name('exposition_selector.set');

        // expositions
        Route::resource('/expositions', 'ExpositionsController')->except(['show']);
        Route::delete('/expositions/{id}/visual', 'ExpositionsController@mainVisualDelete')->name('expositions.mainVisualDelete');

        // exhitions
        Route::resource('/exhibitions', 'ExhibitionsController');
        //Route::get('/exhibitions/', 'ExhibitionsController@index')->name('exhibitions.index');

        // exhibitors
        Route::get('/exhibitors/', 'ExhibitorsController@index')->name('exhibitors.index');

        // exhibition_zones
        Route::get('/exhibition/{exhition_id}/zones/create', 'ExhibitionZonesController@create')->name('exhibition.zones.create');
        Route::post('/exhibition/{exhition_id}/zones', 'ExhibitionZonesController@store')->name('exhibition.zones.store');
        Route::get('/exhibition/{exhition_id}/zones/{id}/edit', 'ExhibitionZonesController@edit')->name('exhibition.zones.edit');
        Route::put('/exhibition/{exhition_id}/zones/{id}', 'ExhibitionZonesController@update')->name('exhibition.zones.update');
        Route::delete('/exhibition/{exhition_id}/zones/{id}', 'ExhibitionZonesController@destroy')->name('exhibition.zones.destroy');

        // superadmins
        //Route::get('/superadmins/', 'SuperadminsController@index')->name('superadmins.index');
        Route::resource('/superadmins', 'SuperadminsController');
        Route::get('/superadmins/password/reset', 'SuperadminsController@resetPassword')->name('superadmins.reset_password');
        Route::post('/superadmins/password', 'SuperadminsController@updatePassword')->name('superadmins.update_password');
    });
});
