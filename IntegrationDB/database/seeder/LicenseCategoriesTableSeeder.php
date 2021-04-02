<?php

use Illuminate\Database\Seeder;
use App\Models\LicenseCategory;
use App\Services\RandomValueGenerator;

use Illuminate\Support\Facades\DB;

class LicenseCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
			$seeds = $this->getSeeds();
			// 物理削除対象
			$delete_targets = LicenseCategory::select([
				'license_category_id',
				'name',
				'sort_index',
			])->whereNotIn('license_category_id', $seeds->keys())
				->withTrashed()
				->get()
				->keyBy('license_category_id')
				;

			if ( !$this->confirmation($seeds, $delete_targets) ) {
				print('処理を中止しました。' . PHP_EOL);
				exit();
			}

			DB::beginTransaction();

			try {
				// 物理削除
				foreach( $delete_targets as $license_category_id => $delete_target ) {
					$result = $delete_target->forceDelete();

					if ( empty($result) ) throw new \Exception("Failed to force delete licenseCategory." . print_r($delete_target->toArray(), true));
				}

				// upsert
				foreach( $seeds as $license_category_id => $seed ) {
					$result = LicenseCategory::updateOrCreate(['license_category_id' => $license_category_id], $seed);

					if ( empty($result) ) throw new \Exception("Failed to save licenseCategory." . print_r($seed, true));
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
			1 => ['name' => '第一種運転免許', 'sort_index' => 1],
			2 => ['name' => '第二種運転免許', 'sort_index' => 2],
			3 => ['name' => '自動二輪', 'sort_index' => 3],
			4 => ['name' => '技能', 'sort_index' => 4],
			//0 => ['name' => 'ユーザー項目', 'sort_index' => 9999],
			5 => ['name' => '独立', 'sort_index' => 5],
			6 => ['name' => '情報処理', 'sort_index' => 6],
			7 => ['name' => '事務系', 'sort_index' => 7],
			8 => ['name' => 'その他運転系', 'sort_index' => 8],
			9 => ['name' => '公務員', 'sort_index' => 9],
			10 => ['name' => '福祉', 'sort_index' => 10],
			11 => ['name' => '医療・看護', 'sort_index' => 11],
			12 => ['name' => 'コンサルタント', 'sort_index' => 12],
			13 => ['name' => '環境', 'sort_index' => 13],
			14 => ['name' => '国際', 'sort_index' => 14],
			15 => ['name' => '建造建設', 'sort_index' => 15],
			16 => ['name' => '工業', 'sort_index' => 16],
			17 => ['name' => '無線', 'sort_index' => 17],
			18 => ['name' => 'その他', 'sort_index' => 18],
			999 => ['name' => 'フリー入力', 'sort_index' => 999],
		]);
	}

	private function confirmation($seeds, $delete_targets) {
			print('以下の内容で登録・更新・（物理）削除します。' . PHP_EOL);
			print('========== 登録・更新 ==========' . PHP_EOL);
			$this->printRows($seeds);

			print('========== 削除 ==========' . PHP_EOL);
			$this->printRows($delete_targets->toArray());

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