<?php

use Illuminate\Database\Seeder;
use App\Models\JobCareerStatus;
use App\Services\RandomValueGenerator;
use Illuminate\Support\Facades\DB;

class JobCareerStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$seeds = $this->getSeeds();
		$CurrentRecords = JobCareerStatus::whereIn('job_career_status_id', $seeds->keys())->get()->keyBy('job_career_status_id');
		//$new_records = [];

		if ( !$this->confirmation($seeds) ) {
			print('処理を中止しました。' . PHP_EOL);
			exit();
		}

		DB::beginTransaction();

		try {
			foreach( $seeds as $key => $value ) {
				// 既存の登録データは更新、新規は登録
				//if ( isset($CurrentRecords[$key]) ) {
				//	$result = DB::table('job_career_statuses')->where('job_career_status_id', $key)->update($value);
				//} else {
				//	$new_record = [
				//		'job_career_status_id' => $key,
				//		'name' => $value['name'],
				//		'display_name' => $value['display_name'],
				//		'sort_index' => $key,
				//		'deleted_at' => $value['deleted_at'] ?? null,
				//	];

				//	$result = DB::table('job_career_statuses')->insert($new_record);
				//}

				//$value['job_career_status_id'] = $key;
				$result = DB::table('job_career_statuses')->updateOrInsert(['job_career_status_id' => $key], $value);

				//$new_records[] = [
				//	'job_career_status_id' => $key,
				//	'name' => $value['name'],
				//	'display_name' => $value['display_name'],
				//	'sort_index' => $key,
/*
					'link_key' => RandomValueGenerator::generateLinkKey(),
					'last_updated_system_id' => config('constants.system.THIS_SYSTEM_ID'),
*/
				//];
				if( empty($result) ) throw new \Exception("Failed to save JobCareerStatus." . print_r($value, true));
			}

			DB::commit();
		} catch( \Exception $e ) {
			DB::rollBack();
			logger()->error($e->getMessage());
			print("LicenseCategoriesTableSeederの実行に失敗しました。{$e->getMessage()}" . PHP_EOL);
		}

		//if( empty($record) ) JobCareerStatus::insert($new_records);
    }

	private function getSeeds() {
		return collect([
			1 => ['name' => '入社', 'display_name' => '入社'],
			2 => ['name' => '退社', 'display_name' => '退社'],
			3 => ['name' => '入職', 'display_name' => '入職', 'deleted_at' => '2020-09-09'],
			4 => ['name' => '退職', 'display_name' => '退職', 'deleted_at' => '2020-09-09'],
			5 => ['name' => '休職', 'display_name' => '休職'],
			6 => ['name' => '復職', 'display_name' => '復職'],
			7 => ['name' => '出向', 'display_name' => '出向'],
			8 => ['name' => '帰任', 'display_name' => '帰任'],
			9 => ['name' => '設立', 'display_name' => '設立'],
			10 => ['name' => '閉鎖', 'display_name' => '閉鎖'],
			11 => ['name' => '開業', 'display_name' => '開業'],
			12 => ['name' => '廃業', 'display_name' => '廃業'],
			13 => ['name' => 'その他', 'display_name' => 'その他'],
		]);
	}

	private function confirmation($seeds) {
			print('以下の内容で登録・更新(論理削除)します' . PHP_EOL);
			print('========== 登録・更新 ==========' . PHP_EOL);
			$this->printRows($seeds);

			print(PHP_EOL . '本当に実行しますか？[y/n] ');
			$stdin = trim(fgets(STDIN));

			return preg_match('/^(y|Y).*$/', $stdin);
	}

	private function printRows($rows) {
			foreach( $rows as $key => $row ) {
				$line = "[{$key}] ";

				foreach( $row as $column => $value ) {
					$line .= "{$column}: {$value} ";
				}

				print($line . PHP_EOL);
			}
	}
}