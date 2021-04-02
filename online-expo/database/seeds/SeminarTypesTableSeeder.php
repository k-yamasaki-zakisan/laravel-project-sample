<?php

use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class SeminarTypesTableSeeder extends Seeder
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
                $result = DB::table('seminar_types')->updateOrInsert(['id' => $seed['id']], $seed);

                if (empty($result)) throw new \Exception("Failed to save SeminarTypes." . print_r($seed, true));
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            print("SeminarTypesTableSeederの実行に失敗しました。{$e->getMessage()}" . PHP_EOL);
        }
    }

    private function getSeeds()
    {
        return collect([
            1 => ['id' => 1, 'name' => '展示会セミナー'],
            2 => ['id' => 2, 'name' => '専門セミナー'],
        ]);
    }
}
