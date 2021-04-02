<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UserExhibitorTableSeeder extends Seeder
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
            'user_id' => 1,
            'exhibitor_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $data[] = [
            'user_id' => 1,
            'exhibitor_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        // umemura
        $data[] = [
            'user_id' => 3,
            'exhibitor_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $data[] = [
            'user_id' => 3,
            'exhibitor_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $data[] = [
            'user_id' => 5,
            'exhibitor_id' => 3,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        DB::table('user_exhibitor')->insert($data);
    }
}
