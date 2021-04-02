<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('employee_id');
            $table->unsignedBigInteger('corporation_id')->comment('法人マスタID');
            $table->unsignedBigInteger('person_id')->nullable()
                ->comment('人マスタID。personsテーブルと連結。1個人を特定するためのID。転職先等を紐づける事ができる。');
            $table->string('code', 20)->nullable()->comment('従業員コード');
            $table->string('last_name', 64)->comment('氏名（苗字）');
            $table->string('first_name', 64)->comment('氏名（名前）');
            $table->string('last_name_kana', 64)->comment('氏名カナ（苗字）');
            $table->string('first_name_kana', 64)->comment('氏名カナ（名前）');
            $table->date('birthday')->nullable()->comment('生年月日');
            $table->date('hire_date')->nullable()->comment('入社年月日');
            $table->date('retirement_date')->nullable()->comment('退職年月日');

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
        Schema::dropIfExists('employees');
    }
}
