<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeJobCareersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_job_careers', function (Blueprint $table) {
            $table->bigIncrements('employee_job_career_id');
            $table->unsignedBigInteger('corporation_id')->comment('法人マスタID');
            $table->unsignedBigInteger('employee_id')->comment('社員マスタID');
            $table->string('last_name', 64)->comment('氏名（苗字）');
            $table->string('first_name', 64)->comment('氏名（名前）');
            $table->string('last_name_kana', 64)->comment('氏名カナ（苗字）');
            $table->string('first_name_kana', 64)->comment('氏名カナ（名前）');
            $table->unsignedSmallInteger('employment_status_id')->comment('雇用形態マスタID');
            $table->unsignedSmallInteger('job_career_status_id')->nullable()->comment('職歴ステータスマスタID');
            $table->text('note')->nullable()->comment('備考');
            $table->timestamp('applied_at')->comment('適用年月日');

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
        Schema::dropIfExists('employee_job_careers');
    }
}
