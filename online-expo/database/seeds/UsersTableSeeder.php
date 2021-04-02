<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

use App\User;

class UsersTableSeeder extends Seeder
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
            'email' => 'ando@forefrontservice.co.jp',
            'password' => Hash::make('ando....'),
            'last_name' => 'Ando',
            'first_name' => 'Takeshi',
            'name' => 'Takeshi Ando',
            'zip_code1' => '160',
            'zip_code2' => '0022',
            'prefecture_id' => '13',
            'address' => '新宿区新宿 2-10-7 ',
            'building_name' => 'TOMビル4F',
            'remember_token' => Str::random(10),
            'user_level' => User::USER_LEVEL__EXHIBITOR,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $data[] = [
            'email' => 'ando@dage.jp',
            'password' => Hash::make('ando....'),
            'last_name' => 'Ando2',
            'first_name' => 'Takeshi',
            'name' => 'Takeshi Ando2',
            'zip_code1' => '160',
            'zip_code2' => '0022',
            'prefecture_id' => '',
            'address' => ' ',
            'building_name' => '',
            'remember_token' => Str::random(10),
            'user_level' => User::USER_LEVEL__VISITOR,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        // umemura
        $data[] = [
            'email' => 'umemura@forefrontservice.co.jp',
            'password' => Hash::make('umemura....'),
            'last_name' => 'Umemura',
            'first_name' => 'Tomoki',
            'name' => 'Tomoki Umemura',
            'zip_code1' => '160',
            'zip_code2' => '0022',
            'prefecture_id' => '13',
            'address' => '新宿区新宿 2-10-7 ',
            'building_name' => 'TOMビル4F',
            'remember_token' => Str::random(10),
            'user_level' => User::USER_LEVEL__EXHIBITOR,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $data[] = [
            'email' => 'umemura2@forefrontservice.co.jp',
            'password' => Hash::make('umemura....'),
            'last_name' => 'Umemura2',
            'first_name' => 'Tomoki',
            'name' => 'Tomoki Umemura',
            'zip_code1' => '160',
            'zip_code2' => '0022',
            'prefecture_id' => '',
            'address' => ' ',
            'building_name' => '',
            'remember_token' => Str::random(10),
            'user_level' => User::USER_LEVEL__VISITOR,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $data[] = [
            'email' => 'yamasaki.forefront@gmail.com',
            'password' => Hash::make('yamasaki....'),
            'last_name' => 'Yamasaki',
            'first_name' => 'Kazuyoshi',
            'name' => 'Kazuyoshi Yamasaki',
            'zip_code1' => '160',
            'zip_code2' => '0022',
            'prefecture_id' => '13',
            'address' => '新宿区新宿 2-10-7 ',
            'building_name' => 'TOMビル4F',
            'remember_token' => Str::random(10),
            'user_level' => User::USER_LEVEL__EXHIBITOR,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];


        DB::table('users')->insert($data);
    }
}
