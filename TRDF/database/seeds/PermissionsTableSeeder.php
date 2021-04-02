<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class PermissionsTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		/*
			各認可定義ファイルに記載されているもののうち、DBに登録されていないもののみ登録
			登録済みのものは無視
			DBに登録されているが、定義ファイルに記載されていないものは削除する
		*/

		$permissions = [
			//TRCD管理画面
			'trcd' => config('database.trcd.permissions'),
		];

		$delete_permission_ids = [];

		foreach($permissions as $guard_name => $guard_permissions){
			// 現在DB上に存在するGuard単位での同名permissionリスト(['permission_name' => permission_id]の形式)
			$duplicate_guard_permissions = DB::table('permissions')->where('guard_name', $guard_name)->whereIn('name', $guard_permissions)->pluck('id', 'name');
			// 新しく追加するGuard単位でのpermissionリストに含まれていないもの（DB上から削除されるもの）(['permission_name' => permission_id]の形式)
			$delete_guard_permissions = DB::table('permissions')->where('guard_name', $guard_name)->whereNotIn('name', $guard_permissions)->pluck('id', 'name');

/*
print('重複しているもの（すでに存在しているので今回は挿入しないもの）--------------------------------------------------'.PHP_EOL);
var_dump($duplicate_guard_permissions);
print('削除対象(定義ファイルに挙がっていないためDBから削除するもの)--------------------------------------------------'.PHP_EOL);
var_dump($delete_guard_permissions);
*/
			//登録リストをフィルタリング
			$permissions[$guard_name] = Arr::where($permissions[$guard_name], function($value, $key) use($duplicate_guard_permissions) {
				return !isset($duplicate_guard_permissions[$value]);
			});
			//削除対象にIDを追加
			$delete_permission_ids = $delete_guard_permissions->toArray();
		}

		//新規挿入データ生成
		$rows = [];
		$now = \Carbon\Carbon::now();
		$now = $now->format('Y-m-d H:i:s');

		foreach ( $permissions as $guard_name => $guard_permissions ) {
			foreach( $guard_permissions as $guard_permission ) {
				$rows[] = [
					'guard_name' => $guard_name,
					'name' => $guard_permission,
					'created_at' => $now,
					'updated_at' => $now,
				];
			}
		}

/*
print('削除するもの--------------------------------------------------'.PHP_EOL);
var_dump($delete_permission_ids);
print('新しく追加するもの--------------------------------------------------'.PHP_EOL);
var_dump($rows);
*/

		DB::beginTransaction();

		try {
			if ( !empty($delete_permission_ids) ) DB::table('permissions')->whereIn('id', $delete_permission_ids)->delete();
			if ( !empty($rows) ) DB::table('permissions')->insert($rows);
		} catch(\Exception $e) {
			var_dump($e->getMessage());
			DB::rollBack();

			return;
		}

		DB::commit();
	}
}
