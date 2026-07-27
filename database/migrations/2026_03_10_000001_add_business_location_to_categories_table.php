<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBusinessLocationToCategoriesTable extends Migration
{
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'business_location')) {
                $table->text('business_location')->nullable()->after('slug');
            } else {
                $table->text('business_location')->nullable()->change();
            }
        });
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'business_location')) {
                $table->dropIndex(['business_location']);
                $table->dropColumn('business_location');
            }
        });
    }
}

