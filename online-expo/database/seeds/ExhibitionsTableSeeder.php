<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ExhibitionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [];

        $data[] = [
            'id' => 1,
            'exposition_id' => 1,
            'name' => 'ODEX 2021',
            'sort_index' => 1,
            //'satori_tag' => 'asdfasdf',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        $data[] = [
            'id' => 2,
            'exposition_id' => 1,
            'name' => 'TELEX2021',
            'sort_index' => 2,
            //'satori_tag' => 'asdfasdf',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $data[] = [
            'id' => 3,
            'exposition_id' => 2,
            'name' => '住宅ビジネスフェア2021',
            'sort_index' => 1,
            //'satori_tag' => 'asdfasdf',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        $data[] = [
            'id' => 4,
            'exposition_id' => 2,
            'name' => 'マンションビジネスフェア2021',
            'sort_index' => 2,
            //'satori_tag' => 'asdfasdf',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        DB::table('exhibitions')->insert($data);
    }
}
