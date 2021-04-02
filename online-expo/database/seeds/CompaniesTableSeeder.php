<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('companies')->insert([
            'name' => '株式会社フォアフロントサービス',
            'name_kana' => 'フォアフロントサービス',
            //'address' => '東京都新宿区新宿2-10-7 TOMビル4F',
            'zip_code1' => '160',
            'zip_code2' => '0022',
            'prefecture_id' => 1,
            'address' => '新宿区新宿2-10-7',
            'building_name' => 'TOMビル4F',
            'url' => 'https://www.forefrontservice.co.jp/',
            'forgin_sync_key' => 'zzzzzzzz',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
