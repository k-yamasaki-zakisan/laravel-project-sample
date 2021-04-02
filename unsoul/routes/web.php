<?php

use Illuminate\Support\Facades\Route;

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

//Route::get('/', function () { return view('welcome'); });

Route::redirect('/login', route('saml2_login', 'doraever'))->name('login');
Route::GET('/login/select', 'SSO\SSOController@selectLoginUser')->name('unsoul.login.select');
Route::POST('/login/select', 'SSO\SSOController@setLoginUser')->name('unsoul.login.set');
Route::redirect('/logout', route('saml2_logout', 'doraever'))->name('logout');

Route::middleware(['ssoauth'])->group(function() {
    // ホーム
    //Route::redirect('/home', '/');
    Route::GET('/', 'HomeController@index')->name('unsoul.home');
    // マイページ
Route::GET('/mypage/edit', 'MyPageController@edit_self')->name('unsoul.mypage.edit_self');
Route::PUT('/mypage/update', 'MyPageController@update_self')->name('unsoul.mypage.update_self');
Route::GET('/mypage/corporation', 'MyPageController@edit_corporation')->name('unsoul.mypage.corporations.edit');
Route::GET('/mypage/offices/register', 'MyPageController@register_office')->name('unsoul.mypage.offices.register');
Route::GET('/mypage/offices/{link_key}', 'MyPageController@edit_office')->name('unsoul.mypage.offices.edit');
Route::POST('/mypage/offices', 'MyPageController@store_office')->name('unsoul.mypage.offices.store');
Route::PUT('/mypage/offices/{link_key}', 'MyPageController@update_office')->name('unsoul.mypage.offices.update');
Route::DELETE('/mypage/offices/{link_key}', 'MyPageController@delete_office')->name('unsoul.mypage.offices.delete');
Route::PUT('/mypage/corporation', 'MyPageController@update_corporation')->name('unsoul.mypage.corporations.update');
    // 法人
    Route::GET('/corporations', 'CorporationController@index')->name('unsoul.corporations.index');
    Route::GET('/corporations/register', 'CorporationController@register')->name('unsoul.corporations.register');
    Route::POST('/corporations/register/confirm', 'CorporationController@confirm')->name('unsoul.corporations.confirm');
    Route::POST('/corporations/register', 'CorporationController@store')->name('unsoul.corporations.store');
    //Route::GET('/corporations/{id}', 'CorporationController@edit')->name('unsoul.corporations.edit');
    //Route::PUT('/corporations/{link_key}', 'CorporationController@update')->name('unsoul.corporations.update');
    //Route::delete('/corporations/{link_key}', 'CorporationController@delete')->name('unsoul.corporations.delete');
    // 事業所
    Route::GET('/corporations/{corporation_id}/offices', 'OfficeController@index')->name('unsoul.offices.index');
    // 人
    Route::GET('/persons', 'PersonController@index')->name('unsoul.persons.index');
    Route::match(['get', 'post'],'/persons/register','PersonController@register')->name('unsoul.persons.register');
    Route::GET('/persons/register/search_corporation', 'PersonController@search_corporation')->name('unsoul.persons.register.search_corporation');
    Route::POST('/persons/register/confirm','PersonController@confirm')->name('unsoul.persons.confirm');
    Route::POST('/persons/store', 'PersonController@store')->name('unsoul.persons.store');
    Route::GET('/persons/{link_key}/password/reset', 'PersonController@resetPassword')->name('unsoul.persons.password');
    Route::POST('/persons/{link_key}/password/reset', 'PersonController@doResetPassword')->name('unsoul.persons.password.reset');
	// 労務管理システム
	Route::GET('/labors', 'LaborController@index')->name('unsoul.labors.index');
	// 従業員
	Route::GET('/employees', 'EmployeeController@index')->name('unsoul.employees.index');
	Route::GET('/employees/register', 'EmployeeController@register')->name('unsoul.employees.register');
	Route::POST('/employees/register/confirm', 'EmployeeController@confirmRegister')->name('unsoul.employees.register.confirm');
	Route::POST('/employees/register', 'EmployeeController@store')->name('unsoul.employees.store');
	Route::DELETE('/employees/{link_key}', 'EmployeeController@delete')->name('unsoul.employees.delete');
	Route::GET('/employees/{link_key}', 'EmployeeController@edit')->name('unsoul.employees.edit');
	Route::PUT('/employees/{link_key}', 'EmployeeController@update')->name('unsoul.employees.update');
    // 資格
Route::GET('/persons/{person_id}/licenses', 'LicenseController@index')->name('unsoul.person_licenses.index');
Route::GET('/persons/{person_id}/licenses/register', 'LicenseController@register')->name('unsoul.licenses.register');
Route::GET('persons/{person_id}/licenses/register_free_license', 'LicenseController@register_free_license')->name('unsoul.licenses.register_free_license');
Route::GET('/persons/{person_id}/licenses/{license_id}', 'LicenseController@edit')->name('unsoul.licenses.edit');
Route::PUT('/persons/{person_id}/licenses/{person_license_link_key}', 'LicenseController@update_person_license')->name('unsoul.licenses.update');
Route::DELETE('/persons/{person_id}/licenses/{person_license_link_key}', 'LicenseController@delete_person_license')->name('unsoul.licenses.delete');
Route::POST('/persons/{person_id}/licenses', 'LicenseController@store_person_license')->name('unsoul.licenses.store');
Route::POST('/persons/{person_id}/free_licenses', 'LicenseController@save_person_free_license')->name('unsoul.licenses.free_save');
});

Auth::routes([
    'login' => false,
    'logout' => false,
    'register' => false,
    'reset' => false,
    'confirm' => false,
]);
