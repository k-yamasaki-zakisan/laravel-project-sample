<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyPhoneticOnOfficesTableToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->string('phonetic', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 既存データにnullが既に入っている場合はここで止まる
        // nullのものは空文字として扱うということで良ければコメントアウト解除
        /*
		DB::table('offices')
			->whereNull('phonetic')
			->update(['phonetic' => ''])
		;
*/
        Schema::table('offices', function (Blueprint $table) {
            $table->string('phonetic', 255)->nullable(false)->change();
        });
    }
}
