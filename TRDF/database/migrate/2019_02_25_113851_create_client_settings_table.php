<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_settings', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->integer('client_id')->unsigned()->comment('クライアントID');
            // NULL許容
			$table->string('google_maps_api_key')->nullable()->comment('GoogleマップAPIキー');
			//$table->boolean('edit_flag')->default(false)->comment('編集フラグ');
            $table->timestamps();
						// INDEX
						//$table->index(['client_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_settings');
    }
}
