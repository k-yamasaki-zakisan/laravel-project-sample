<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountTitleClientsTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$param = [
			'client_id' => 196,
			'account_title_id' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		];
		DB::table('account_title_clients')->insert($param);

		$param = [
                        'client_id' => 196,
                        'account_title_id' => 2,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                ];
                DB::table('account_title_clients')->insert($param);

		$param = [
                        'client_id' => 196,
                        'account_title_id' => 3,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                ];
                DB::table('account_title_clients')->insert($param);
	}
}
