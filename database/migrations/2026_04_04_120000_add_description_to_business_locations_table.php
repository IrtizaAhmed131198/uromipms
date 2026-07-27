<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('business_locations', function (Blueprint $table) {
            if (! Schema::hasColumn('business_locations', 'description')) {
                $table->text('description')->nullable()->after('custom_field4');
            }
        });
    }

    public function down()
    {
        Schema::table('business_locations', function (Blueprint $table) {
            if (Schema::hasColumn('business_locations', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
