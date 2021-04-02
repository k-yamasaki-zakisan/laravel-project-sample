<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExhibitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exhibitors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('exhibition_id')->nullable(false)->comment('展示会ID');
            $table->integer('exhibition_zone_id')->nullable(false)->comment('展示会ゾーンID');
            //$table->integer('exhibitor_id')->nullable(false)->comment('出展企業ID');
            $table->integer('company_id')->nullable(false)->comment('出展企業ID');

            //$table->string('login_name', 100)->nullable(false)->comment('ログイン名');
            //$table->string('password' )->nullable(false)->comment('パスワード');
            $table->string('name', 100)->nullable(false)->comment('出展企業名');
            $table->string('name_kana', 100)->nullable(false)->comment('出展企業名カナ');
            $table->string('name_kana_for_sort', 100)->nullable(false)->comment('ソート順のための出展企業名カナ');

            //$table->string('address', 500)->nullable(false)->comment('住所');

            $table->string('zip_code1')->nullable(false)->comment('郵便番号1');
            $table->string('zip_code2')->nullable(false)->comment('郵便番号2');
            $table->integer('prefecture_id')->nullable(true)->comment('都道府県');
            $table->string('address')->nullable(true)->comment('住所1');
            $table->string('building_name')->nullable(true)->comment('建物名');

            $table->string('tel', 20)->nullable(false)->comment('TEL');
            $table->string('url', 300)->nullable(true)->comment('サイトURL');
            $table->string('profile_text', 2000)->nullable(true)->comment('プロフィール文章');

            $table->string('forgin_sync_key', 100)->nullable(true)->comment('連動の為の外部同期キー。当面は基幹システムの出展社ID');

            $table->softDeletes();

            $table->timestamps();
        });


        DB::statement("COMMENT ON TABLE exhibitors IS '出展企業ごとに１データ。'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exhibitors');
    }
}
