<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SuperadminsTableSeeder extends Seeder
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
            'name' => 'Takeshi Ando(Superadmin)',
            'email' => 'ando@forefrontservice.co.jp',
            'password' => Hash::make('ando....'),
            'remember_token' => Str::random(10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        $data[] = [
            'name' => 'Motoki Umemura(Superadmin)',
            'email' => 'umemura@forefrontservice.co.jp',
            'password' => Hash::make('umemura....'),
            'remember_token' => Str::random(10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];


        DB::table('superadmins')->insert($data);
    }
}
