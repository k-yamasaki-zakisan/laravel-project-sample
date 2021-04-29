<?php

namespace App\Services\CsvImport;

// Services
use App\Services\ServiceBase;
// Models
use App\Exhibition;
use App\ExhibitionZone;
use App\Exhibitor;
use App\Prefecture;
use App\Company;
use App\Plan;
use App\User;
// Utilities
use DB;
use Illuminate\Support\Facades\Hash;

class ExhibitorCsvImportService extends ServiceBase {

	/*
		csvデータを抜き取りとDBに登録処理
	*/
	public function expoJoinExhibitorCsvImport($csv_path) {
		// ファイルの存在確認
		//if (( fopen($csv_path, "r")) === FALSE )  return false;

		// ファイルの拡張子確認(csvのみ)
		if ( !preg_match("/\.csv$/", $csv_path) ) {
			logger()->error("Only .csv extension is supported.");
			return false;
		}

		$fp = new \SplFileObject($csv_path);
		$fp->setFlags(
			\SplFileObject::READ_CSV |
			\SplFileObject::READ_AHEAD |
			\SplFileObject::SKIP_EMPTY |
			\SplFileObject::DROP_NEW_LINE
		);

/*
		$i = 0;
		$headers = [];
		$body = [];

		foreach ( $fp as $line ) {
			// 1列目未入力の場合はスキップ
			if ( !mb_strlen($line[0]) ) continue;

			// 先頭行はヘッダ行とみなして処理
			if ( $i === 0 ) {
				// 項目名と列番号のマッピング
				$headers = array_flip($line);
				$i++;
				continue;
			}

			$body[] = $line;
		}
*/
		$headers = [];
		$body = [];

		foreach ( $fp as $idx => $line ) {
			// 先頭行はヘッダ行とみなして処理(項目名と列番号のマッピング)
			if ( $idx === 0 ) $headers = array_flip($line);
			else $body[] = $line;
		}

		// ヘッダ部検閲
		if ( !$this->__CensorHeaders($headers) ) {
			logger()->error("Invalid headers." . print_r($headers, true));
			return false;
		}

		logger()->info('$headers = ' . print_r($headers, true));

		// 行検閲
		$prefectures = Prefecture::pluck('id', 'name')->toArray();

		foreach( $body as $idx => $line ) {
			try {
				$body[$idx] = $this->__CensorLine($line, $headers, $prefectures);
			} catch( \Throwable $e ) {
				logger()->error("Invalid line. [idx:{$idx}] {$e->getMessage()} in {$e->getFile()} at {$e->getLine()}");
				return false;
			}
		}



		// インポート処理
		DB::beginTransaction();

		try {
			foreach( $body as $idx => $line ) {
				// Companyインポート処理
				$company_data = [
					'name' => $line['customer_name'],
					'name_kana' => $line['customer_kana'],
					'zip_code1' => $line['customer_zip_code1'],
					'zip_code2' => $line['customer_zip_code2'],
					'prefecture_id' => $line['prefecture_id'],
					'address' => $line['customer_address'],
					'building_name' => $line['customer_building'],
					'url' => $line['customer_url'],
					'foreign_sync_key' => $line['customer_id'],
					'phone_number' => $line['customer_tel'],
				];
				$Company = $this->__SaveCompany($company_data);

				// 保存成否判定
				if ( empty($Company) ) throw new \Exception("Failed to save Company.[idx:{$idx}] company_data = " . print_r($company_data, true));

				logger()->info('Company = ' . print_r($Company->toArray(), true));



				// Exhibition存在チェック
				$Exhibition = Exhibition::where('import_code', $line['exhibition_name'])->first();

				if ( empty($Exhibition) ) throw new \Exception("There are no exhibitions with import_code {$line['exhibition_name']}.");

				// ExhibitionZone取得
				$ExhibitionZone = ExhibitionZone::where('exhibition_id', $Exhibition->id)->where('name', $line['zone'])->first();

				// Plan取得
				// ToDo: 曖昧検索->完全一致
				//$Plan = Plan::where('display_name', 'like', "%{$line['plan_name']}%")->first();
				$Plan = Plan::where('display_name', "{$line['plan_name']}")->first();



				// Exhibitorインポート処理
				$exhibitor_data = [
					'exhibition_id' => $Exhibition->id,
					'exhibition_zone_id' => $ExhibitionZone->id ?? null,
					'company_id' => $Company->id,
					'name' => $line['customer_name'],
					'name_kana' => $line['customer_kana'],
					'name_kana_for_sort' => $line['customer_kana'],
					'zip_code1' => $line['customer_zip_code1'],
					'zip_code2' => $line['customer_zip_code2'],
					'prefecture_id' => $line['prefecture_id'],
					'address' => $line['customer_address'],
					'building_name' => $line['customer_building'],
					'tel' => $line['customer_tel'],
					'url' => $line['customer_url'],
					'foreign_sync_key' => $line['exhibitor_id'],
					'plan_id' => $Plan->id ?? null,
				];
				$Exhibitor = $this->__SaveExhibitor($exhibitor_data);

				// 保存成否判定
				if ( empty($Exhibitor) ) throw new \Exception("Failed to save Exhibitor.[idx:{$idx}] exhibitor_data = " . print_r($exhibitor_data, true));

				logger()->info('Exhibitor = ' . print_r($Exhibitor->toArray(), true));



				// @2021.04.26 YuKaneko 管理ユーザー登録処理
				$user_first_name = '管理者アカウント';
				$user_data = [
					'email' => $line['email'],
					'password' => Hash::make($line['exhibitor_password']),
					'last_name' => $line['customer_name'],
					'first_name' => $user_first_name,
					// ToDo: 揺らぎが生じそうなのでどこかしらでロジックの統一をした方が良い
					'name' => "{$line['customer_name']} {$user_first_name}",
					//'email_verified_at' => ?,
					'zip_code1' => $line['customer_zip_code1'],
					'zip_code2' => $line['customer_zip_code2'],
					'prefecture_id' => $line['prefecture_id'],
					'address' => $line['customer_address'],
					'building_name' => $line['customer_building'],
					//'remember_token' => ?,
					//'user_level' => User::USER_LEVEL__EXHIBITOR,
					'phone_number' =>  $line['customer_tel'],
					//'mobile_phone_number' => ?,
					'company_id' => $Company->id,
				];
				$User = $this->__SaveUser($user_data);

				// 保存成否判定
				if ( empty($User) ) throw new \Exception("Failed to save User.[idx:{$idx}] user_data = " . print_r($user_data, true));

				logger()->info('User = ' . print_r($User->toArray(), true));

				// 中間テーブル更新
				// syncすると既存データが失われるため、attach
				$User->exhibitors()->attach([$Exhibitor->id]);

//logger($User->exhibitors->toArray());
/*
				// 郵便番号抽出
				$zip_code = $line[$headers['顧客郵便番号']];
				$zip_code_array = explode('-', $zip_code);
				$zip_code1 = $zip_code_array[0];
				$zip_code2 = $zip_code_array[1];

				// 都道府県検索
				$prefecture_name = $line[$headers['顧客都道府県']];
				$Prefecture = Prefecture::where('name', $prefecture_name)->first();

				// Companyのインポート処理
				$company_data = [
					'name' => $line[$headers['顧客名']],
					'name_kana' => $line[$headers['顧客カナ']],
					'zip_code1' => $zip_code1,
					'zip_code2' => $zip_code2,
					'prefecture_id' => $Prefecture->id ?? null,
					'address' => $line[$headers['顧客市区町村丁目番地']],
					'building_name' => $line[$headers['顧客建物']],
					'url' => $line[$headers['顧客URL']],
					'foreign_sync_key' => $line[$headers['顧客ID※']],
					'phone_number' => $line[$headers['顧客TEL']],
				];

				// 外部同期キー入力チェック
				if ( !mb_strlen($company_data['foreign_sync_key']) ) throw new \DomainException("To import Company foreign_sync_key is necessary.[idx:{$idx}]");

				// 外部同期キーから既存Company検索
				$Company = Company::where('foreign_sync_key', $company_data['foreign_sync_key'])->first();

				// なければ新規作成
				if ( empty($Company) ) $Company = new Company();

				$Company->fill($company_data);
				$result = $Company->save();

				// 保存成否判定
				if ( empty($result) ) throw new \Exception("Failed to save Company.[idx:{$idx}] company_data = " . print_r($company_data, true));

				logger()->info('Company = ' . print_r($Company->toArray(), true));



				$import_code = $line[$headers['展示会名']];
				// 展示会インポートキー入力チェック
				if ( !mb_strlen($import_code) ) throw new \DomainException("To import Exhibitor import_code is necessary.[idx:{$idx}]");

				$Exhibition = Exhibition::where('import_code', $import_code)->first();

				// Exhibition存在チェック
				if ( empty($Exhibition) ) throw new \Exception("There are no Exhibitions with import_code {$import_code}.");

				$zone_name = $line[$headers['ゾーン']];
				$ExhibitionZone = ExhibitionZone::where('exhibition_id', $Exhibition->id)->where('name', $zone_name)->first();
				// 存在チェック不要？

				//ToDo: 良きタイミングで曖昧検索から完全一致に直した方が良い?
				$plan_name = $line[$headers['プラン']];
				$Plan = Plan::where('display_name', 'like', "%$plan_name%")->first();

				// exhibitorの登録
				$exhibitor_data = [
					'exhibition_id' => $Exhibition->id,
					'exhibition_zone_id' => $ExhibitionZone->id ?? null,
					'company_id' => $Company->id,
					'name' => $line[$headers['顧客名']],
					'name_kana' => $line[$headers['顧客カナ']],
					'name_kana_for_sort' => $line[$headers['顧客カナ']],
					'zip_code1' => $zip_code1,
					'zip_code2' => $zip_code2,
					'prefecture_id' => $Prefecture->id ?? null,
					'address' => $line[$headers['顧客市区町村丁目番地']],
					'building_name' => $line[$headers['顧客建物']],
					'tel' => $line[$headers['顧客TEL']],
					'url' => $line[$headers['顧客URL']],
					'foreign_sync_key' => $line[$headers['出展社ID']],
					'plan_id' => $Plan->id,
				];

				// 外部同期キー入力チェック
				if ( !mb_strlen($exhibitor_data['foreign_sync_key']) ) throw new \DomainException("To import Exhibitor foreign_sync_key is necessary.[idx:{$idx}]");

				// 外部同期キーから既存Exhibitor検索
				$Exhibitor = Exhibitor::where('foreign_sync_key', $exhibitor_data['foreign_sync_key'])->first();

				// なければ新規作成
				if ( empty($Exhibitor) ) $Exhibitor = new Exhibitor();

				$Exhibitor->fill($exhibitor_data);
				$result = $Exhibitor->save();

				// 保存成否判定
				if ( empty($result) ) throw new \Exception("Failed to save Exhibitor.[idx:{$idx}] exhibitor_data = " . print_r($exhibitor_data, true));

				logger()->info('Exhibitor = ' . print_r($Exhibitor->toArray(), true));



				// @2021.04.26 YuKaneko 管理ユーザー登録処理
				// CompanyやExhibitorの値を利用しても良い？
				$user_data = [
					'email' => $line[$headers['メール']] ?? null,
					'password' => Hash::make($line[$headers['出展社PW']]),
					'last_name' => $line[$headers['顧客名']],
					'first_name' => '管理者アカウント',
					//'email_verified_at' => ?,
					'zip_code1' => $zip_code1,
					'zip_code2' => $zip_code2,
					'prefecture_id' => $Prefecture->id ?? null,
					'address' => $line[$headers['顧客市区町村丁目番地']],
					'building_name' => $line[$headers['顧客建物']],
					//'remember_token' => ?,
					//'user_level' => User::USER_LEVEL__EXHIBITOR,
					'phone_number' =>  $line[$headers['顧客TEL']],
					//'mobile_phone_number' => ?,
					'company_id' => $Company->id ?? null,
				];
				// ToDo: 揺らぎが生じそうなのでどこかしらでロジックの統一をした方が良い
				$user_data['name'] = "{$user_data['last_name']} {$user_data['first_name']}";

				// 既存データがあった場合に上書きしてしまって良い？
				$User = User::where('email', $user_data['email'])->first();

				// なければ新規作成
				if ( empty($User) ) $User = new User();

				$User->fill($user_data);
				$result = $User->save();

				// 保存成否判定
				if ( empty($result) ) throw new \Exception("Failed to save User. [idx:{$idx}]");

				logger('User = ' . print_r($User->toArray(), true));

				// 中間テーブル更新
				// syncすると既存データが失われるため、attach
				$User->exhibitors()->attach([$Exhibitor->id]);
logger($User->exhibitors->toArray());
*/
			}

			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
			return false;
		}

		return true;
	}

	/*
		ヘッダ部検閲
		@param Array $headers
		@return bool
	*/
	private function __CensorHeaders(Array $headers) : bool {
		// 必須項目名
		$requires = [
			'展示会名',
			'顧客ID※',
			'出展社ID',
			'出展社PW',
			'顧客名',
			'顧客カナ',
			//'展示会（カテゴリ扱い）',
			'ゾーン',
			'顧客郵便番号',
			'顧客都道府県',
			'顧客市区町村丁目番地',
			'顧客建物',
			'顧客TEL',
			'顧客URL',
			'小間番号',
			'メール',
			'プラン',
		];

		// 検査
		foreach( $requires as $key ) {
			if ( !isset($headers[$key]) ) {
				logger()->error("{$key} is missing. in ".__FILE__." at ".__LINE__);
				return false;
			}
		}

		return true;
	}

	/*
		行検閲・整形
		@param $line
		@param $headers
		@param $prefectures
		@param $plans
		@return Array
	*/
	private function __CensorLine(Array $raw_line, Array $headers, Array $prefectures) : Array {
		$line = [];

		foreach( $raw_line as $col_idx => $value ) {
			switch($col_idx) {
				case($headers['展示会名']):
					if ( !$this->__FilledIn($value) ) throw new \DomainException("exhibition_name is not filled in.");
					$line['exhibition_name'] = $value;
					break;
				case($headers['顧客ID※']):
					if ( !$this->__FilledIn($value) ) throw new \DomainException("customer_id is not filled in.");
					$line['customer_id'] = $value;
					break;
				case($headers['出展社ID']):
					if ( !$this->__FilledIn($value) ) throw new \DomainException("exhibitor_id is not filled in.");
					$line['exhibitor_id'] = $value;
					break;
				case($headers['出展社PW']):
					if ( !$this->__FilledIn($value) ) throw new \DomainException("exhibitor_password is not filled in.");
					$line['exhibitor_password'] = $value;
					break;
				case($headers['顧客名']):
					if ( !$this->__FilledIn($value) ) throw new \DomainException("customer_name is not filled in.");
					$line['customer_name'] = $value;
					break;
				case($headers['顧客カナ']):
					if ( !$this->__FilledIn($value) ) throw new \DomainException("customer_kana is not filled in.");
					$line['customer_kana'] = $value;
					break;
//				case($headers['展示会（カテゴリ扱い）']):
//					$line['exhibition_category'] = $this->__FilledIn($value) ? $value : null;
//					break;
				case($headers['ゾーン']):
					$line['zone'] = $this->__FilledIn($value) ? $value : null;
					break;
				case($headers['顧客郵便番号']):
					if ( !$this->__FilledIn($value) ) throw new \DomainException("customer_zip_code is not filled in.");
					// 空白除去
					$value = trim(mb_convert_kana($value, 's'));
					if ( !preg_match('/^\d{3}-\d{4}$/', $value) ) throw new \DomainException("customer_zip_code format is invalid. [{$value}]");

					$zip_code_array = explode('-', $value);
					$line['customer_zip_code1'] = $zip_code_array[0];
					$line['customer_zip_code2'] = $zip_code_array[1];
					break;
				case($headers['顧客都道府県']):
					if ( $this->__FilledIn($value) ) {
						if ( !isset($prefectures[$value]) ) throw new \DomainException("Invalid prefecture name. [{$value}]");
						$line['prefecture_name'] = $value;
						$line['prefecture_id'] = $prefectures[$value];
					} else {
						$line['prefecture_name'] = null;
						$line['prefecture_id'] = null;
					}
					break;
				case($headers['顧客市区町村丁目番地']):
					$line['customer_address'] = $this->__FilledIn($value) ? $value : null;
					break;
				case($headers['顧客建物']):
					$line['customer_building'] = $this->__FilledIn($value) ? $value : null;
					break;
				case($headers['顧客TEL']):
					if ( !$this->__FilledIn($value) ) throw new \DomainException("customer_tel is not filled in.");
					$line['customer_tel'] = $value;
					break;
				case($headers['顧客URL']):
					$line['customer_url'] = $this->__FilledIn($value) ? $value : null;
					break;
				case($headers['小間番号']):
					$line['booth_number'] = $this->__FilledIn($value) ? $value : null;
					break;
				case($headers['メール']):
					if ( !$this->__FilledIn($value) ) throw new \DomainException("email is not filled in.");
					$line['email'] = $value;
					break;
				case($headers['プラン']):
					if ( !$this->__FilledIn($value) ) throw new \DomainException("plan is not filled in.");
					$line['plan_name'] = $value;
					break;
				default:
					break;
			}
		}

		return $line;
	}

	/*
		入力判定
		@?string $value
		@return bool
	*/
	private function __FilledIn(?string $value) : bool {
		return mb_strlen($value) ? true : false;
	}

	/*
		Company保存処理
		@param Array $data
		@return Company | false
	*/
	private function __SaveCompany(Array $data) {
		// 外部同期キー入力チェック
		if ( !$this->__FilledIn($data['foreign_sync_key']) ) {
			logger()->error("Company foreign_sync_key is not filled in. in ".__FILE__." at ".__LINE__);
			return false;
		}

		// 外部同期キーから既存Company検索
		$Company = Company::where('foreign_sync_key', $data['foreign_sync_key'])->first();

		// なければ新規作成
		if ( empty($Company) ) $Company = new Company();

		$Company->fill($data);
		// ToDo: Validate
		return $Company->save() ? $Company : false;
	}

	/*
		Exhibitor保存処理
		@param Array $data
		@return Exhibitor | false
	*/
	private function __SaveExhibitor(Array $data) {
		// 外部同期キー入力チェック
		if ( !$this->__FilledIn($data['foreign_sync_key']) ) {
			logger()->error("Exhibitor foreign_sync_key is not filled in. in ".__FILE__." at ".__LINE__);
			return false;
		}

		// 外部同期キーから既存Exhibitor検索
		$Exhibitor = Exhibitor::where('foreign_sync_key', $data['foreign_sync_key'])->first();

		// なければ新規作成
		if ( empty($Exhibitor) ) $Exhibitor = new Exhibitor();

		$Exhibitor->fill($data);
		// ToDo: Validate
		return $Exhibitor->save() ? $Exhibitor : false;
	}

	/*
		User保存処理
		@param Array $data
		@return User | false
	*/
	private function __SaveUser(Array $data) {
		// email入力チェック
		if ( !$this->__FilledIn($data['email']) ) {
			logger()->error("User email is not filled in. in ".__FILE__." at ".__LINE__);
			return false;
		}

		$User = User::where('email', $data['email'])->first();

		// 暫定処理
		if ( empty($User) ) {
			// 既存データがない場合は新規作成
			$User = new User();
			$User->fill($data);
		} else {
			// 既存データがある場合は何もせず終了
			return $User;
		}

		// ToDo: Validate
		return $User->save() ? $User : false;
	}
}