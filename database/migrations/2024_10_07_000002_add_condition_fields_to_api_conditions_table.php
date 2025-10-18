<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_api_conditions', function (Blueprint $table) {
            $table->string('operator', 20)->after('field_name')->nullable()->comment('Operator for condition (EXISTS, =, !=, etc.)');
            $table->text('value')->after('operator')->nullable()->comment('Value to compare against');
            $table->string('action_type', 50)->after('value')->nullable()->comment('Type of action to perform');
            $table->json('action_rules')->after('condition_rules')->nullable()->comment('Rules for action execution');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tb_api_conditions', function (Blueprint $table) {
            $table->dropColumn(['operator', 'value', 'action_type', 'action_rules']);
        });
    }
};