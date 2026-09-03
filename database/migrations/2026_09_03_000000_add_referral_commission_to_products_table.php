<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferralCommissionToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'referral_commission_type')) {
                $table->string('referral_commission_type', 20)->default('percentage')->after('warranty_id')
                      ->comment('Type of referral commission: percentage or fixed');
            }
            if (!Schema::hasColumn('products', 'referral_commission_amount')) {
                $table->decimal('referral_commission_amount', 22, 4)->default(0)->after('referral_commission_type')
                      ->comment('Staff referral bonus/commission amount per unit or percentage');
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
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'referral_commission_amount')) {
                $table->dropColumn('referral_commission_amount');
            }
            if (Schema::hasColumn('products', 'referral_commission_type')) {
                $table->dropColumn('referral_commission_type');
            }
        });
    }
}
