<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRejectionReasonToInventoryRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('inventory_requests') && !Schema::hasColumn('inventory_requests', 'rejection_reason')) {
            Schema::table('inventory_requests', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable()->after('notes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('inventory_requests') && Schema::hasColumn('inventory_requests', 'rejection_reason')) {
            Schema::table('inventory_requests', function (Blueprint $table) {
                $table->dropColumn('rejection_reason');
            });
        }
    }
}
