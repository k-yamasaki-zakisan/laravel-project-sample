<?php

use Illuminate\Database\Seeder;
use App\Models\EmploymentStatus;
use App\Services\RandomValueGenerator;
use Illuminate\Support\Facades\DB;

class EmploymentStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$seeds = $this->getSeeds();

		if ( !$this->confirmation($seeds) ) {
			print('処理を中止しました。' . PHP_EOL);
			exit();
		}

		DB::beginTransaction();

		try {
			foreach( $seeds as $key => $value ) {
				$result = DB::table('employment_statuses')->updateOrInsert(['employment_status_id' => $key], $value);

				if( empty($result) ) throw new \Exception("Failed to save EmploymentStatus." . print_r($value, true));
    			}
		DB::commit();
		} catch( \Exception $e ) {
			DB::rollBack();
			logger()->error($e->getMessage());
			print("LicenseCategoriesTableSeederの実行に失敗しました。{$e->getMessage()}" . PHP_EOL);
		}
    }

	private function getSeeds() {
		return collect([
			1 => ['employment_status_id' => 1, 'name' => '代表', 'deleted_at' => '2020-09-09'],
			2 => ['employment_status_id' => 2, 'name' => '正社員'],
			3 => ['employment_status_id' => 3, 'name' => 'アルバイト'],
			4 => ['employment_status_id' => 4, 'name' => 'パート'],
			5 => ['employment_status_id' => 5, 'name' => '契約社員'],
			6 => ['employment_status_id' => 6, 'name' => '派遣社員'],
			7 => ['employment_status_id' => 7, 'name' => '業務委託'],
			8 => ['employment_status_id' => 8, 'name' => 'その他'],
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