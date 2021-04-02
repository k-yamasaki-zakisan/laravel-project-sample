<?php

use Illuminate\Database\Seeder;
use App\Models\EducationalBackgroundStatus;
use App\Services\RandomValueGenerator;

class EducationalBackgroundStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$seeds = $this->getSeeds();
		$CurrentRecords = EducationalBackgroundStatus::whereIn('educational_background_status_id', $seeds->keys())->get()->keyBy('educational_background_status_id');
		$new_records = [];

		foreach( $seeds as $key => $value ) {
			// 新規システム以外は無視
			if ( isset($CurrentRecords[$key]) ) continue;

			$new_records[] = [
				'educational_background_status_id' => $key,
				'name' => $value['name'],
				'display_name' => $value['display_name'],
				'sort_index' => $key,
/*
				'link_key' => RandomValueGenerator::generateLinkKey(),
				'last_updated_system_id' => config('constants.system.THIS_SYSTEM_ID'),
*/
			];
		}

		if( !empty($new_records) ) EducationalBackgroundStatus::insert($new_records);
    }

	private function getSeeds() {
		return collect([
			1 => ['name' => '入学', 'display_name' => '入学'],
			2 => ['name' => '卒業', 'display_name' => '卒業'],
			3 => ['name' => '卒業見込み', 'display_name' => '卒業見込'],
			4 => ['name' => '修了', 'display_name' => '修了'],
			5 => ['name' => '転入学', 'display_name' => '転入'],
			6 => ['name' => '編入学', 'display_name' => '編入'],
			7 => ['name' => '休学', 'display_name' => '休学'],
			8 => ['name' => '中途退学', 'display_name' => '中退'],
			9 => ['name' => '退学', 'display_name' => '退学'],
			10 => ['name' => 'その他', 'display_name' => 'その他'],
		]);
	}
}