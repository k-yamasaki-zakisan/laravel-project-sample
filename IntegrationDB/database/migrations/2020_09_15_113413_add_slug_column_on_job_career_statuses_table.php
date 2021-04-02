<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class AddSlugColumnOnJobCareerStatusesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// 最初はNULL許容にしておく
		Schema::table('job_career_statuses', function (Blueprint $table) {
			$table->string('slug', 32)->nullable(true)->after('name')->comment('ラベル文字列（主キー特定用途を想定）');
		});

		// 既存データ更新
		$this->updateExistingData();

		// NOT NULL UNIQUEに変更
		Schema::table('job_career_statuses', function (Blueprint $table) {
			$table->string('slug', 32)->nullable(false)->unique()->after('name')->comment('ラベル文字列（主キー特定用途を想定）')->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('job_career_statuses', function (Blueprint $table) {
			$table->dropColumn('slug');
		});
	}

	private function updateExistingData()
	{
		// 既存データ取得
		$data = DB::table('job_career_statuses')
			->select(['job_career_status_id', 'name'])
			->orderBy('job_career_status_id')
			->get();

		foreach ($data as $obj) {
			$slug = null;

			switch ($obj->name) {
				case ('入社'):
					$slug = 'join';
					break;
				case ('退社'):
					$slug = 'resignation';
					break;
				case ('入職'):
					$slug = 'employment';
					break;
				case ('退職'):
					$slug = 'retirement';
					break;
				case ('休職'):
					$slug = 'suspension';
					break;
				case ('復職'):
					$slug = 'reinstatement';
					break;
				case ('出向'):
					$slug = 'seconded';
					break;
				case ('帰任'):
					$slug = 'return';
					break;
				case ('設立'):
					$slug = 'establishment';
					break;
				case ('閉鎖'):
					$slug = 'closed';
					break;
				case ('開業'):
					$slug = 'opening';
					break;
				case ('廃業'):
					$slug = 'cessation';
					break;
				case ('その他'):
					$slug = 'other';
					break;
			}

			$result = DB::table('job_career_statuses')
				->where('job_career_status_id', $obj->job_career_status_id)
				->update(['slug' => $slug]);

			if (empty($result)) throw new \RuntimeException("更新に失敗。{$obj->job_career_status_id} {$slug}.");

			print("{$obj->job_career_status_id}:{$slug}" . PHP_EOL);
		}
	}
}
