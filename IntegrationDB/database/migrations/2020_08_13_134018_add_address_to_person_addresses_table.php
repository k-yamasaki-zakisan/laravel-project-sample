<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressToPersonAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('person_addresses', function (Blueprint $table) {
            $table->string('address', 255)->nullable()->after('building')->comment('検索用に住所を連結して格納');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('person_addresses', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }
}
