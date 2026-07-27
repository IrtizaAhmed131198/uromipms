<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'check_in')) {
                $table->dateTime('check_in')->nullable()->after('hms_coupon_id');
            }
            if (!Schema::hasColumn('transactions', 'check_out')) {
                $table->dateTime('check_out')->nullable()->after('check_in');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {});
    }
};
