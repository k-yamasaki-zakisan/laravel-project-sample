<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contact_request_type_id')->nullable(false)->comment('問い合わせ返信タイプID');
            $table->integer('exhibitor_id')->nullable(false)->comment('展示社ID');
            $table->integer('user_id')->nullable(false)->comment('ユーザーID');
            $table->string('body', 2000)->nullable(false)->comment('問い合わせ内容');
            $table->string('status_text')->nullable(true)->comment('ステータス内容');
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
        Schema::dropIfExists('contacts');
    }
}
