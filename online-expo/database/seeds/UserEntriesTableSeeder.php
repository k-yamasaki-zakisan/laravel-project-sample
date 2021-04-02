<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UserEntriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [];

        // ando
        $data[] = [
            'user_id' => 2,
            //'exposition_id' => 1,
            'exhibition_id' => 1,
            'registered_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        // umemura
        $data[] = [
            'user_id' => 4,
            //'exposition_id' => 1,
            'exhibition_id' => 1,
            'registered_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];


        DB::table('user_entries')->insert($data);
    }
}
