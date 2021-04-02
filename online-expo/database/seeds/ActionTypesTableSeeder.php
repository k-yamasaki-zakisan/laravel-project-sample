<?php

use Illuminate\Database\Seeder;

class ActionTypesTableSeeder extends Seeder
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
                $result = DB::table('action_types')->updateOrInsert(['id' => $seed['id']], $seed);

                if (empty($result)) throw new \Exception("Failed to save ActionTypes." . print_r($seed, true));
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            print("ActionTypesTableSeederの実行に失敗しました。{$e->getMessage()}" . PHP_EOL);
        }
    }

    private function getSeeds()
    {
        return collect([
            1  => ['id' =>  1, 'name' => 'EXPOログイン',           'code' => 100],
            2  => ['id' =>  2, 'name' => '出展社モーダルOPEN',     'code' => 200],
            3  => ['id' =>  3, 'name' => '出展社モーダルCLOSE',    'code' => 209],
            4  => ['id' =>  4, 'name' => '出展社詳細ページOPEN',   'code' => 210],
            5  => ['id' =>  5, 'name' => '出展社動画PLAY',         'code' => 211],
            6  => ['id' =>  6, 'name' => '出展社動画STOP',         'code' => 212],
            7  => ['id' =>  7, 'name' => '製品ページOPEN',         'code' => 220],
            8  => ['id' =>  8, 'name' => '製品動画PLAY',           'code' => 221],
            9  => ['id' =>  8, 'name' => '製品動画STOP',           'code' => 222],
            10 => ['id' => 10, 'name' => '製品ファイルDOWNLOAD',   'code' => 223],
            11 => ['id' => 11, 'name' => 'お問い合わせページOPEN', 'code' => 230],
            12 => ['id' => 12, 'name' => 'お問い合わせSUBMIT',     'code' => 234],
            13 => ['id' => 13, 'name' => 'チャットページOPEN',     'code' => 240],
            14 => ['id' => 14, 'name' => 'チャットSUBMIT',         'code' => 244],
            15 => ['id' => 15, 'name' => 'セミナーモーダルOPEN',   'code' => 300],
            16 => ['id' => 16, 'name' => 'セミナーモーダルCLOSE',  'code' => 309],
            17 => ['id' => 17, 'name' => 'セミナー動画PLAY',       'code' => 301],
            18 => ['id' => 18, 'name' => 'セミナー動画STOP',       'code' => 302],
        ]);
    }
}
