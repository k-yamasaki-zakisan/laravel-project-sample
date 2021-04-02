<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::group([
	'middleware' => [
			'auth-system:api-system',
			'UnescapeJsonResponse',
	],
], function() {
	// システム
	Route::group(['prefix' => 'systems'], function() {
		Route::get('', 'API\SystemController@index')->name('systems.index');
	});
	// 人
	Route::get('persons', 'API\PersonController@find')->name('api.persons.find');
	Route::post('persons_list', 'API\PersonController@getList')->name('api.persons.list');
	Route::post('store/persons', 'API\PersonController@store')->name('api.persons.store'); // 登録
	Route::get('get_person_all_data', 'API\PersonController@getWithRelated')->name('api.persons.with_related');
	Route::get('persons/search', 'API\PersonController@personSearchWithPerson_full_nameAndCorporation_name')->name('api.persons.search.with.person_full_name.and.corporation_name');
	Route::post('password_reset', 'API\PersonController@reset_password')->name('api.persons.reset_password');
	Route::post('update/persons', 'API\PersonController@update')->name('api.persons.update'); //更新
	// 性別
	Route::get('genders', 'API\GenderController@find')->name('api.genders.find');
	Route::post('genders_list', 'API\GenderController@getList')->name('api.genders.list');
	// 人の住所
	Route::get('person_addresses', 'API\PersonAddressController@find')->name('api.person_addresses.find');
	Route::post('person_addresses_list', 'API\PersonAddressController@getList')->name('api.person_addresses.list');
	// 学歴
	Route::get('educational_backgrounds', 'API\EducationalBackgroundController@find')->name('api.educational_backgrounds.find');
	Route::post('educational_backgrounds_list', 'API\EducationalBackgroundController@getList')->name('api.educational_backgrounds.list');
	// 学校種別
	Route::get('school_types', 'API\SchoolTypeController@find')->name('api.school_types.find');
	Route::post('school_types_list', 'API\SchoolTypeController@getList')->name('api.school_types.list');
	// 学歴ステータス
	Route::get('educational_background_statuses', 'API\EducationalBackgroundStatusController@find')->name('api.educational_background_statuses.find');
	Route::post('educational_background_statuses_list', 'API\EducationalBackgroundStatusController@getList')->name('api.educational_background_statuses.list');
	// 人の履歴
	Route::get('person_job_careers', 'API\PersonJobCareerController@find')->name('api.person_job_careers.find');
	Route::post('person_job_careers_list', 'API\PersonJobCareerController@getList')->name('api.person_job_careers.list');
	// 人の連絡先
	Route::get('person_contacts', 'API\PersonContactController@find')->name('api.person_contacts.find');
	Route::post('person_contacts_list', 'API\PersonContactController@getList')->name('api.person_contacts.list');
	// 所有資格
	Route::get('person_licenses', 'API\PersonLicenseController@find')->name('api.person_licenses.find');
	Route::post('person_licenses_list', 'API\PersonLicenseController@getList')->name('api.person_licenses.list');
	Route::post('store/person_licenses', 'API\PersonLicenseController@store')->name('api.person_licenses.store');  //登録
	Route::post('update/person_licenses', 'API\PersonLicenseController@update')->name('api.person_licenses.update');  //更新
	Route::post('delete/person_licenses', 'API\PersonLicenseController@delete')->name('api.person_licenses.delete');  //削除
	// 法人
	Route::get('corporations', 'API\CorporationController@find')->name('api.corporations.find');
	Route::post('store/corporations', 'API\CorporationController@store')->name('api.corporations.store'); // 登録
	Route::post('corporations_list', 'API\CorporationController@getList')->name('api.corporations.list');
	Route::get('get_corpration_all_data', 'API\CorporationController@getWithRelated')->name('api.corporations.with_related');
	Route::get('corporations/search', 'API\CorporationController@corporationsSearchWithCorporation_nameAndOffice_address')->name('api.corporations.search.with.corporation_name.and.office_address'); //運soul人登録用企業検索API
	Route::post('update/corporations', 'API\CorporationController@update')->name('api.corporations.update');  //更新
	// 法人種別
	Route::get('corporation_types', 'API\CorporationTypeController@find')->name('api.corporation_types.find');
	Route::post('corporation_types_list', 'API\CorporationTypeController@getList')->name('api.corporation_types.list');
	// 職歴ステータス
	Route::get('job_career_statuses', 'API\JobCareerStatusController@find')->name('api.job_career_statuses.find');
	Route::post('job_career_statuses_list', 'API\JobCareerStatusController@getList')->name('api.job_career_statuses.list');
	// 連絡先種別
	Route::get('contact_types', 'API\ContactTypeController@find')->name('api.contact_types.find');
	Route::post('contact_types_list', 'API\ContactTypeController@getList')->name('api.contact_types.list');
	// 事業所
	Route::get('offices', 'API\OfficeController@find')->name('api.offices.find');
	Route::post('offices_list', 'API\OfficeController@getList')->name('api.offices.list');
	Route::get('get_office_all_data', 'API\OfficeController@getWithRelated')->name('api.offices.with_related');
	Route::post('store/offices', 'API\OfficeController@store')->name('api.offices.store');  // 登録
	Route::post('update/offices', 'API\OfficeController@update')->name('api.offices.update');  // 更新
	Route::post('delete/offices', 'API\OfficeController@delete')->name('api.offices.delete');  // 削除
	// 事業所連絡先
	Route::get('office_contacts', 'API\OfficeContactController@find')->name('api.office_contacts.find');
	Route::post('office_contacts_list', 'API\OfficeContactController@getList')->name('api.office_contacts.list');
	// 社員（従業員）
	Route::get('employees', 'API\EmployeeController@find')->name('api.employees.find');
	Route::post('employees_list', 'API\EmployeeController@getList')->name('api.employees.list');
	Route::get('get_employee_all_data', 'API\EmployeeController@getWithRelated')->name('api.employees.with_related');
	Route::post('delete/employees', 'API\EmployeeController@delete')->name('api.employees.delete');
	Route::post('store/employees', 'API\EmployeeController@store')->name('api.employees.store');
  	Route::post('update/employees', 'API\EmployeeController@update')->name('api.employees.update');
	// 社員(従業員)の住所
        Route::get('employee_addresses', 'API\EmployeeAddressController@find')->name('api.employee_addresses.find');
        Route::post('employee_addresses_list', 'API\EmployeeAddressController@getList')->name('api.employee_addresses.list');
	//社員（従業員）の連絡先
	Route::get('employee_contacts', 'API\EmployeeContactController@find')->name('api.employee_contacts.find');
        Route::post('employee_contacts_list', 'API\EmployeeContactController@getList')->name('api.employee_contacts.list');
	// 労務システム
	Route::post('search/employees', 'API\EmployeeController@search')->name('api.employees.search');
	// 法人所属履歴
	Route::get('employee_job_careers', 'API\EmployeeJobCareerController@find')->name('api.employee_job_careers.find');
	Route::post('employee_job_careers_list', 'API\EmployeeJobCareerController@getList')->name('api.employee_job_careers.list');
	// 雇用形態
	Route::get('employment_statuses', 'API\EmploymentStatusController@find')->name('api.employment_statuses.find');
	Route::post('employment_statuses_list', 'API\EmploymentStatusController@getList')->name('api.employment_statuses.list');
	// 都道府県
	Route::get('prefectures', 'API\PrefectureController@find')->name('api.prefectures.find');
	Route::post('prefectures_list', 'API\PrefectureController@getList')->name('api.prefectures.list');
	// 車両
	Route::get('vehicles', 'API\VehicleController@find')->name('api.vehicles.find');
	Route::post('vehicles_list', 'API\VehicleController@getList')->name('api.vehicles.list');
	// 車両の温度帯
	Route::get('temperature_zone_vehicles', 'API\TemperatureZoneVehicleController@find')->name('api.temperature_zone_vehicles.find');
	Route::post('temperature_zone_vehicles_list', 'API\TemperatureZoneVehicleController@getList')->name('api.temperature_zone_vehicles.list');
	// 温度帯
	Route::get('temperature_zones', 'API\TemperatureZoneController@find')->name('api.temperature_zones.find');
	Route::post('temperature_zones_list', 'API\TemperatureZoneController@getList')->name('api.temperature_zones.list');
	// 車両形状
	Route::get('vehicle_forms', 'API\VehicleFormController@find')->name('api.vehicle_forms.find');
	Route::post('vehicle_forms_list', 'API\VehicleFormController@getList')->name('api.vehicle_forms.list');
	// 資格
	Route::get('licenses', 'API\LicenseController@find')->name('api.licenses.find');
	Route::post('licenses_list', 'API\LicenseController@getList')->name('api.licenses.list');
	Route::post('search/licenses', 'API\LicenseController@search')->name('api.licenses.search');
	Route::post('edit/licenses', 'API\LicenseController@edit')->name('api.licenses.edit');
	Route::post('search/licenses_list', 'API\LicenseController@searchList')->name('api.licenses.search_list');
	// 資格カテゴリ
	Route::post('license_categories_list', 'API\LicenseController@getLicenseCategoryList')->name('api.licenses.license_categories_list');
	// ドラ採用求職者
        Route::get('dora_hires', 'API\DoraHireController@find')->name('api.dora_hires.find');
        Route::post('dora_hires_list', 'API\DoraHireController@getList')->name('api.dora_hires.list');
});