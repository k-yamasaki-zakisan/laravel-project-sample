<?php

use Illuminate\Database\Seeder;

class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seeds = $this->getSeeds();

        DB::beginTransaction();

        try {
            foreach ($seeds as $seed) {
                $result = DB::table('plans')->updateOrInsert($seed);

                if (empty($result)) throw new \Exception("Failed to save plans." . print_r($seed, true));
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            print("PlansTableSeederの実行に失敗しました。{$e->getMessage()}" . PHP_EOL);
        }
    }

    private function getSeeds()
    {
        return collect([
            [
                'id' => 1,
                'name' => 'entry',
                'display_name' => 'エントリープラン（無料）',
                'product_publish_count' => 1,
                'view_real_exhibition_flag' => false,
                'view_real_catalog_flag' => false,
                'view_online_exhibition_flag' => true,
                'accept_using_chat_flag' => false
            ],
            [
                'id' => 2,
                'name' => 'light',
                'display_name' => 'ライトプラン',
                'product_publish_count' => 1,
                'view_real_exhibition_flag' => false,
                'view_real_catalog_flag' => false,
                'view_online_exhibition_flag' => true,
                'accept_using_chat_flag' => true
            ],
            [
                'id' => 3,
                'name' => 'special_exhibition',
                'display_name' => '特別出展プラン',
                'product_publish_count' => 5,
                'view_real_exhibition_flag' => false,
                'view_real_catalog_flag' => true,
                'view_online_exhibition_flag' => true,
                'accept_using_chat_flag' => true
            ],
            [
                'id' => 4,
                'name' => 'real_exhibition',
                'display_name' => 'リアル展示会出展プラン',
                'product_publish_count' => 5,
                'view_real_exhibition_flag' => true,
                'view_real_catalog_flag' => false,
                'view_online_exhibition_flag' => true,
                'accept_using_chat_flag' => true
            ],
        ]);
    }
}
