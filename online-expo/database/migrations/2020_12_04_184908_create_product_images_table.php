<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->nullable(false)->comment('企業ID');
            $table->string('image_path')->nullable(false)->comment('画像パス');
            $table->integer('sort_index')->nullable(false)->comment('ソート順');
            $table->timestamps();
        });


        DB::statement("COMMENT ON TABLE product_images IS '製品画像'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_images');
    }
}
