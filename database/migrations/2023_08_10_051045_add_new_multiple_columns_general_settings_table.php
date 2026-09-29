<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            //
            $table->string('site_footer_logo', 250)->nullable();

            $table->string('home_page_youtube_link', 250)->nullable();
            $table->string('home_page_youtube_code', 250)->nullable();

            $table->float('cgst_percent', 10, 2);
            $table->float('sgst_percent', 10, 2);


            $table->float('igst_percent', 10, 2);

            $table->float('stumento_commision_percent', 10, 2);





            $table->string('footer_link_name2', 250)->nullable();


            $table->string('footer_link2', 250)->nullable();
            //$table->string('footer_link2', 250)->nullable();


            //$table->string('footer_link2', 250)->nullable();






        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            //
        });
    }
};
