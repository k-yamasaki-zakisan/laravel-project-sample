<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicenseCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('license_categories', function (Blueprint $table) {
            $table->smallIncrements('license_category_id');
            $table->string('name', 32)->unique()->comment('資格カテゴリ名');
            $table->unsignedInteger('sort_index')->nullable()->comment('ソート順');
            //			$table->char('link_key', 64)->unique()->comment('外部システムとの連携の為のキー');
            //			$table->unsignedSmallInteger('last_updated_system_id')->comment('最終更新者');
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
        Schema::dropIfExists('license_categories');
    }
}
