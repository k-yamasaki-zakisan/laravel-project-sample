<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expositions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->nullable(false)->comment('イベント名');
            $table->date('start_date')->nullable(false)->comment('イベント開始日付');
            $table->integer('exposition_days')->nullable(false)->comment('開催日数');
            $table->string('slug', 100)->unique()->nullable(false)->comment('URLのスラッグ');
            $table->boolean('active_flag')->nullable(false)->default(true)->comment('展示会ページを表示するか。ログインができるか');
            $table->boolean('can_pre_registration_flag')->nullable(false)->default(true)->comment('事前登録の受け付けをするか');
            $table->string('main_visual_path')->nullable(true)->comment('イベントトップ画像');
            $table->softDeletes();

            //$table->string('import_code', 100)->nullable(false)->comment('インポートキー');
            $table->timestamps();
        });


        DB::statement("COMMENT ON TABLE expositions IS '展示会マスタ。展示会１回につき１レコード'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expositions');
    }
}
