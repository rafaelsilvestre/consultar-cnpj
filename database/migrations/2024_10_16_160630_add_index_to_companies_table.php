<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->index('cnpj');
            $table->index('fantasy_name');
            $table->index('registration_status');
            $table->index('main_activity');
            $table->index(['registration_status', 'main_activity']);
            $table->index(['cnpj', 'fantasy_name']);
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
            $table->dropIndex('companies_cnpj_index');
            $table->dropIndex('companies_fantasy_name_index');
            $table->dropIndex('companies_registration_status_index');
            $table->dropIndex('companies_main_activity_index');
            $table->dropIndex('companies_registration_status_main_activity_index');
            $table->dropIndex('companies_cnpj_fantasy_name_index');
        });
    }
}
