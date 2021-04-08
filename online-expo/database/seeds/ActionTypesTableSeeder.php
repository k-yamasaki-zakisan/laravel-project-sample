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
                $result = DB::table('action_types')->updateOrInsert($seed);

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
            ['name' => 'EXPOログイン',           'code' => 100],
            ['name' => '出展社モーダルOPEN',     'code' => 200],
            ['name' => '出展社モーダルCLOSE',    'code' => 209],
            ['name' => '出展社詳細ページOPEN',   'code' => 210],
            ['name' => '出展社動画PLAY',         'code' => 211],
            ['name' => '出展社動画STOP',         'code' => 212],
            ['name' => '製品ページOPEN',         'code' => 220],
            ['name' => '製品動画PLAY',           'code' => 221],
            ['name' => '製品動画STOP',           'code' => 222],
            ['name' => '製品ファイルDOWNLOAD',   'code' => 223],
            ['name' => 'お問い合わせページOPEN', 'code' => 230],
            ['name' => 'お問い合わせSUBMIT',     'code' => 234],
            ['name' => 'チャットページOPEN',     'code' => 240],
            ['name' => 'チャットSUBMIT',         'code' => 244],
            ['name' => 'セミナーモーダルOPEN',   'code' => 300],
            ['name' => 'セミナーモーダルCLOSE',  'code' => 309],
            ['name' => 'セミナー動画PLAY',       'code' => 301],
            ['name' => 'セミナー動画STOP',       'code' => 302],
        ]);
    }
}
