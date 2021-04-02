<?php
return [
	// 単一レコード取得系
	'FIND_CORPORATION' => ['method' => 'GET', 'path' => 'corporations'],
	'FIND_EMPLOYEE' => ['method' => 'GET', 'path' => 'employees'],
	'FIND_PERSON' => ['method' => 'GET', 'path' => 'persons'],
	'FIND_OFFICE' => ['method' => 'GET', 'path' => 'offices'],
	// 一覧取得系
	'PERSON_LIST' => ['method' => 'POST', 'path' => 'persons_list'],
	'CORPORATION_LIST' => ['method' => 'POST', 'path' => 'corporations_list'],
	'EMPLOYEE_LIST' => ['method' => 'POST', 'path' => 'employees_list'],
	'CORPORATION_TYPE_LIST' => ['method' => 'POST', 'path' => 'corporation_types_list'],
	'GENDER_LIST' => ['method' => 'POST', 'path' => 'genders_list'],
	'EMPLOYMENT_STATUS_LIST' => ['method' => 'POST', 'path' => 'employment_statuses_list'],
	'LICENSE_LIST' => ['method' => 'POST', 'path' => 'licenses_list'],
	'PERSONLICENSE_LIST' => ['method' => 'POST', 'path' => 'person_licenses_list'],
	'CONTACT_TYPE_LIST' => ['method' => 'POST', 'path' => 'contact_types_list'],
	'JOB_CAREER_STATUS_LIST' => ['method' => 'POST', 'path' => 'job_career_statuses_list'],
	// 労務システム検索取得系
	'EMPLOYEE_SEARCH' => ['method' => 'POST', 'path' => 'search/employees'],
	'PREFECTURE_LIST' => ['method' => 'POST', 'path' => 'prefectures_list'],
	//人関連検索取得系
	'PERSON_SEARCH' => ['method' => 'GET', 'path' => 'persons/search'],
	'PERSON_REGISTER_CORPORATION_SEARCH' => ['method' => 'GET', 'path' => 'corporations/search'],
	// 関連情報取得系
	'FIND_PERSON_WITH_RELATED' => ['method' => 'GET', 'path' => 'get_person_all_data'],
	'FIND_CORPORATION_WITH_RELATED' => ['method' => 'GET', 'path' => 'get_corpration_all_data'],
	'FIND_EMPLOYEE_WITH_RELATED' => ['method' => 'GET', 'path' => 'get_employee_all_data'],
	// 保存系
	'SAVE_CORPORATION' => ['method' => 'POST', 'path' => 'store/corporations'],
	'SAVE_PERSON' => ['method' => 'POST', 'path' => 'store/persons'],
    'SAVE_OFFICE' => ['method' => 'POST', 'path' => 'store/offices'],
	'SAVE_PERSONLICENSE' => ['method' => 'POST', 'path' => 'store/person_licenses'],
	'SAVE_EMPLOYEE' => ['method' => 'POST', 'path' => 'store/employees'],
	// 資格検索取得系
	'LICENSE_SEARCH' => ['method' => 'POST', 'path' => 'search/licenses'],
	'LICENSE_EDIT' => ['method' => 'POST', 'path' => 'edit/licenses'],
	'LICENSE_UPDATE' => ['method' => 'PUT', 'path' => 'update/licenses'],
	//更新系
	'UPDATE_PERSON' => ['method' => 'POST', 'path' => 'update/persons'],
	'UPDATE_OFFICE' => ['method' => 'POST', 'path' => 'update/offices'],
	'UPDATE_CORPORATION' => ['method' => 'POST', 'path' => 'update/corporations'],
	'UPDATE_PERSONLICENSE' => ['method' => 'POST', 'path' => 'update/person_licenses'],
	'UPDATE_EMPLOYEE' => ['method' => 'POST', 'path' => 'update/employees'],
	//削除系
	'DELETE_OFFICE' => ['method' => 'POST', 'path' => 'delete/offices'],
	'DELETE_PERSONLICENSE' => ['method' => 'POST', 'path' => 'delete/person_licenses'],
    'RESET_PASSWORD' => ['method' => 'POST', 'path' => 'password_reset'],
	'DELETE_EMPLOYEE' => ['method' => 'POST', 'path' => 'delete/employees'],
	// 資格カテゴリ取得系
	'LICENSE_CATEGORY_LIST' => ['method' => 'POST', 'path' => 'license_categories_list'],
];