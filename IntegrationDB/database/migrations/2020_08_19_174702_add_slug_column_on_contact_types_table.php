<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class AddSlugColumnOnContactTypesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// 最初はNULL許容にしておく
		Schema::table('contact_types', function (Blueprint $table) {
			$table->string('slug', 32)->nullable(true)->after('name')->comment('ラベル文字列（主キー特定用途を想定）');
		});

		// 既存データ更新
		$this->updateExistingData();

		// NOT NULL UNIQUEに変更
		Schema::table('contact_types', function (Blueprint $table) {
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
		Schema::table('contact_types', function (Blueprint $table) {
			$table->dropColumn('slug');
		});
	}

	private function updateExistingData()
	{
		// 既存データ取得
		$data = DB::table('contact_types')
			->select(['contact_type_id', 'name'])
			->orderBy('contact_type_id')
			->get();

		foreach ($data as $obj) {
			$slug = null;

			switch ($obj->name) {
				case ('固定電話'):
					$slug = 'tel';
					break;
				case ('携帯電話'):
					$slug = 'mobile';
					break;
				case ('FAX'):
					$slug = 'fax';
					break;
				case ('メールアドレス'):
					$slug = 'email';
					break;
			}

			$result = DB::table('contact_types')
				->where('contact_type_id', $obj->contact_type_id)
				->update(['slug' => $slug]);

			if (empty($result)) throw new \RuntimeException("更新に失敗。{$obj->contact_type_id} {$slug}.");

			print("{$obj->contact_type_id}:{$slug}" . PHP_EOL);
		}
	}
}
