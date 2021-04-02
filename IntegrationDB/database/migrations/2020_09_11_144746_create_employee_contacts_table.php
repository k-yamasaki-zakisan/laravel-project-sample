<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_contacts', function (Blueprint $table) {
            $table->bigIncrements('employee_contact_id');
            $table->unsignedBigInteger('employee_id')->comment('社員マスタID');
            $table->unsignedSmallInteger('contact_type_id')->comment('連絡先種別マスタID');
            $table->string('value', 255);
            $table->unsignedInteger('sort_index')->nullable()->comment('ソート順');
            $table->unsignedBigInteger('last_updated_by')->nullable()->comment('最終更新をした人のperson_id');
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
        Schema::dropIfExists('employee_contacts');
    }
}
