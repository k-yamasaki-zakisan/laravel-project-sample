<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExhibitionZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exhibition_zones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->nullable(false)->comment('エリア名');
            $table->integer('exhibition_id')->nullable(false)->comment('展示会ID');
            $table->integer('sort_index')->nullable(false)->comment('ソート順');

            //$table->string('import_code', 100)->nullable(false)->comment('インポートキー');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exhibition_zones');
    }
}
