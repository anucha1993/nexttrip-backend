<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPdfHeaderFooterToApiProviders extends Migration
{
    public function up()
    {
        Schema::table('tb_api_providers', function (Blueprint $table) {
            $table->string('pdf_header')->nullable()->after('headers')->comment('PDF header image path');
            $table->string('pdf_footer')->nullable()->after('pdf_header')->comment('PDF footer image path');
            $table->enum('pdf_header_footer_enabled', ['on', 'off'])->default('off')->after('pdf_footer')->comment('Enable/disable PDF header/footer');
        });
    }

    public function down()
    {
        Schema::table('tb_api_providers', function (Blueprint $table) {
            $table->dropColumn(['pdf_header', 'pdf_footer', 'pdf_header_footer_enabled']);
        });
    }
}
