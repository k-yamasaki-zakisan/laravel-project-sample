<?php

use Illuminate\Database\Seeder;
use App\Models\SchoolType;
use App\Services\RandomValueGenerator;

class SchoolTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$seeds = $this->getSeeds();
		$CurrentRecords = SchoolType::whereIn('school_type_id', $seeds->keys())->get()->keyBy('school_type_id');
		$new_records = [];

		foreach( $seeds as $key => $value ) {
			// 新規システム以外は無視
			if ( isset($CurrentRecords[$key]) ) continue;

			$new_records[] = [
				'school_type_id' => $key,
				'name' => $value['name'],
				'display_name' => $value['display_name'],
				'sort_index' => $key,
/*
				'link_key' => RandomValueGenerator::generateLinkKey(),
				'last_updated_system_id' => config('constants.system.THIS_SYSTEM_ID'),
*/
			];
		}

		if( !empty($new_records) ) SchoolType::insert($new_records);
    }

	private function getSeeds() {
		return collect([
			1 => ['name' => '小学校', 'display_name' => '小学校'],
			2 => ['name' => '中学校', 'display_name' => '中学校'],
			3 => ['name' => '高等学校 ', 'display_name' => '高校'],
			4 => ['name' => '大学', 'display_name' => '大学'],
			5 => ['name' => '短期大学', 'display_name' => '短大'],
			6 => ['name' => '大学院', 'display_name' => '大学院'],
			7 => ['name' => '専門学校', 'display_name' => '専門'],
			8 => ['name' => '高等専門学校', 'display_name' => '高専'],
			9 => ['name' => 'その他', 'display_name' => 'その他'],
		]);
	}
}