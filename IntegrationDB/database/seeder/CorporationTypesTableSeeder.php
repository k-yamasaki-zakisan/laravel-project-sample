<?php

use Illuminate\Database\Seeder;
use App\Models\CorporationType;
use App\Services\RandomValueGenerator;

use Illuminate\Support\Facades\DB;

class CorporationTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
      $seeds = $this->getSeeds();

      if ( !$this->confirmation($seeds) ) {
        print('処理を中止しました。' . PHP_EOL);
        exit();
      }

      DB::beginTransaction();

      try {
        foreach( $seeds as $corporation_type_id => $seed ) {
          $result = CorporationType::updateOrCreate(['corporation_type_id' => $corporation_type_id], $seed);

          if ( empty($result) ) throw new \Exception("Failed to save licenseCategory." . print_r($seed, true));
        }

        DB::commit();
      } catch( \Exception $e ) {
        	DB::rollBack();
        	logger()->error($e->getMessage());
        	print("CorporationTypesTableSeederの実行に失敗しました。{$e->getMessage()}" . PHP_EOL);
      }

    }

	private function getSeeds() {
		return collect([
			1 => ['name' => '株式会社', 'display_name' => '株式会社', 'sort_index' => 1],
			2 => ['name' => '有限会社', 'display_name' => '有限会社', 'sort_index' => 2],
			3 => ['name' => '合資会社', 'display_name' => '合資会社', 'sort_index' => 3],
			4 => ['name' => '合名会社', 'display_name' => '合名会社', 'sort_index' => 4],
			5 => ['name' => '相互会社', 'display_name' => '相互会社', 'sort_index' => 5],
			6 => ['name' => '医療法人', 'display_name' => '医療法人', 'sort_index' => 6],
			7 => ['name' => '財団法人', 'display_name' => '財団法人', 'sort_index' => 7],
			8 => ['name' => '社団法人', 'display_name' => '社団法人', 'sort_index' => 8],
			9 => ['name' => '社会福祉法人', 'display_name' => '社会福祉法人', 'sort_index' => 9],
			10 => ['name' => '学校法人', 'display_name' => '学校法人', 'sort_index' => 10],
			11 => ['name' => '特定非営利活動法人', 'display_name' => '特定非営利活動法人', 'sort_index' => 11],
			12 => ['name' => 'ＮＰＯ法人', 'display_name' => 'ＮＰＯ法人', 'sort_index' => 12],
			13 => ['name' => '商工組合', 'display_name' => '商工組合', 'sort_index' => 13],
			14 => ['name' => '林業組合', 'display_name' => '林業組合', 'sort_index' => 14],
			15 => ['name' => '同業組合', 'display_name' => '同業組合', 'sort_index' => 15],
			16 => ['name' => '農業協同組合', 'display_name' => '農業協同組合', 'sort_index' => 16],
			17 => ['name' => '漁業協同組合', 'display_name' => '漁業協同組合', 'sort_index' => 17],
			18 => ['name' => '農事組合法人', 'display_name' => '農事組合法人', 'sort_index' => 18],
			19 => ['name' => '生活互助会', 'display_name' => '生活互助会', 'sort_index' => 19],
			20 => ['name' => '協業組合', 'display_name' => '協業組合', 'sort_index' => 20],
			21 => ['name' => '協同組合', 'display_name' => '協同組合', 'sort_index' => 21],
			22 => ['name' => '生活協同組合', 'display_name' => '生活協同組合', 'sort_index' => 22],
			23 => ['name' => '連合会', 'display_name' => '連合会', 'sort_index' => 23],
			24 => ['name' => '組合連合会', 'display_name' => '組合連合会', 'sort_index' => 24],
			25 => ['name' => '協同組合連合会', 'display_name' => '協同組合連合会', 'sort_index' => 25],
			29 => ['name' => '一般社団法人', 'display_name' => '一般社団法人', 'sort_index' => 26],
			30 => ['name' => '公益社団法人', 'display_name' => '公益社団法人', 'sort_index' => 27],
			31 => ['name' => '一般財団法人', 'display_name' => '一般財団法人', 'sort_index' => 28],
			32 => ['name' => '公益財団法人', 'display_name' => '公益財団法人', 'sort_index' => 29],
			33 => ['name' => '合同会社', 'display_name' => '合同会社', 'sort_index' => 30],
			99 => ['name' => '個人又はその他の法', 'display_name' => '個人又はその他の法', 'sort_index' => 31],
		]);
	}

  private function confirmation($seeds) {
      print('以下の内容で登録更新します。' . PHP_EOL);

      foreach( $seeds as $key => $seed ) {
        $line = "[{$key}] ";

        foreach( $seed as $column => $value ) {
          $line .= "{$column}: {$value} ";
        }

        print($line . PHP_EOL);
      }

      print('本当に実行しますか？[y/n] ');
      $stdin = trim(fgets(STDIN));

      return preg_match('/^(y|Y).*$/', $stdin);
  }

}