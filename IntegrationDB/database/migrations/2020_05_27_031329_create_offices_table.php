<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offices', function (Blueprint $table) {
			$table->bigIncrements('office_id');
			$table->unsignedBigInteger('corporation_id')->comment('法人マスタID');
			$table->string('name', 255)->comment('事業所名');
			$table->string('phonetic', 255)->comment('フリガナ');
			$table->char('zip_code1', 3)->nullable()->comment('郵便番号上３桁');
			$table->char('zip_code2', 4)->nullable()->comment('郵便番号下４桁');
			$table->unsignedSmallInteger('prefecture_id')->nullable()->comment('都道府県マスタID');
			$table->string('city', 100)->nullable()->comment('市区');
			$table->string('town', 100)->nullable()->comment('町村');
			$table->string('street', 100)->nullable()->comment('番地');
			$table->string('building', 50)->nullable()->comment('建物名・部屋番号');

			$table->char('link_key', 64)->unique()->comment('外部システムとの連携の為のキー');
			$table->unsignedSmallInteger('last_updated_system_id')->comment('最終更新者');
			$table->softDeletes()->comment('削除日時');
			$table->timestamp('created_at')->useCurrent()->comment('作成日時');
			$table->timestamp('updated_at')->useCurrent()->comment('更新日時');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offices');
    }
}