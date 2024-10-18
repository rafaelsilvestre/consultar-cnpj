<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('type')
                ->after('id')
                ->nullable();

            $table->string('name')
                ->after('type')
                ->nullable();

            $table->date('start_of_activity')
                ->after('main_activity')
                ->nullable();

            $table->string('address_type_of_street', 50)
                ->after('start_of_activity')
                ->nullable();

            $table->string('address_street')
                ->after('address_type_of_street')
                ->nullable();

            $table->string('address_number')
                ->after('address_street')
                ->nullable();

            $table->string('address_additional')
                ->after('address_number')
                ->nullable();

            $table->string('address_neighborhood')
                ->after('address_additional')
                ->nullable();

            $table->string('address_zip_code')
                ->after('address_neighborhood')
                ->nullable();

            $table->string('address_state')
                ->after('address_zip_code')
                ->nullable();

            $table->string('address_city')
                ->after('address_state')
                ->nullable();

            $table->string('phone_number')
                ->after('address_city')
                ->nullable();

            $table->string('alternative_phone_number')
                ->after('phone_number')
                ->nullable();

            $table->string('email')
                ->after('alternative_phone_number')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('name');
            $table->dropColumn('start_of_activity');
            $table->dropColumn('address_type_of_street');
            $table->dropColumn('address_street');
            $table->dropColumn('address_number');
            $table->dropColumn('address_additional');
            $table->dropColumn('address_neighborhood');
            $table->dropColumn('address_zip_code');
            $table->dropColumn('address_state');
            $table->dropColumn('address_city');
            $table->dropColumn('phone_number');
            $table->dropColumn('alternative_phone_number');
            $table->dropColumn('email');
        });
    }
}
