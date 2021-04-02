<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExhibitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exhibitions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('exposition_id')->nullable(false)->comment('博覧会ID');
            $table->string('name', 200)->nullable(false)->comment('展示会名（１回のEXPOで場所ごとに複数の展示会がある）');
            $table->integer('sort_index')->nullable(false)->comment('ソート順');
            //$table->string('satori_tag', 100)->nullable(false)->comment('SATORIとの連動時に利用するタグ');

            //$table->string('import_code', 100)->nullable(false)->comment('インポートキー');

            $table->softDeletes();

            $table->timestamps();
        });


        DB::statement("COMMENT ON TABLE exhibitions IS '展示会マスタ（１回の展示会でも複数の展示会がある）'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exhibitions');
    }
}
