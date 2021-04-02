<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Person;

class AddFullNameColumnOnPersonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 最初からNOT NULLだとエラーになるのでダミー文字列挿入
        Schema::table('persons', function (Blueprint $table) {
            $table->string('full_name', 128)->default('TEST')->after('first_name_kana')->comment('検索用氏名');
        });

        // NOT NULLに変更
        Schema::table('persons', function (Blueprint $table) {
            $table->string('full_name', 128)->default(null)->after('first_name_kana')->comment('検索用氏名')->change();
        });

        // 既存データの姓名からフルネームを更新
        //$ColPersons = Person::select(['person_id', 'first_name', 'last_name'])->orderBy('person_id')->get();
        $ColPersons = Person::orderBy('person_id')->get();

        foreach ($ColPersons as $Person) {
            $full_name = "{$Person['last_name']}{$Person['first_name']}";
            $result = $Person->update(['full_name' => $full_name]);

            if (empty($result)) throw new \RuntimeException("更新に失敗。{$Person['person_id']} {$full_name}.");

            print("{$Person['person_id']}:{$full_name}" . PHP_EOL);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('full_name');
        });
    }
}
