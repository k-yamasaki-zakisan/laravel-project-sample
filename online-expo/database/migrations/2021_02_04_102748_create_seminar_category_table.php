<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeminarCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('SeminarCategory', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('exposition_id')->nullable(false)->comment('EXPOID');
            $table->integer('exhibition_id')->nullable(true)->comment('展示会ID');
            $table->integer('seminar_type_id')->nullable(false)->comment('セミナータイプID');
            $table->string('name')->nullable(false)->comment('セミナーカテゴリー名');
            $table->boolean('active_flag')->nullable(false)->default(true)->comment('セミナーカテゴリを表示するか');
            $table->integer('sort_index')->nullable(false)->comment('ソート順');
            $table->softDeletes();
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
        Schema::dropIfExists('SeminarCategory');
    }
}
