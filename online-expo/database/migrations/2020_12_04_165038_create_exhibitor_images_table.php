<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExhibitorImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exhibitor_images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('exhibitor_id')->nullable(false)->comment('企業ID');
            $table->string('image_path')->nullable(false)->comment('画像パス');
            $table->integer('sort_index')->nullable(false)->comment('ソート順');
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
        Schema::dropIfExists('exhibitor_images');
    }
}
