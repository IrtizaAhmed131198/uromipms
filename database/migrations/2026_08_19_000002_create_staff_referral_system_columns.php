<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffReferralSystemColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'referral_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('referral_code', 50)->nullable()->unique()->after('username')
                      ->comment('Unique referral code for staff bonuses');
            });
        }

        Schema::table('business', function (Blueprint $table) {
            if (!Schema::hasColumn('business', 'default_referral_commission_percent')) {
                $table->decimal('default_referral_commission_percent', 5, 2)->default(0)->after('default_sales_discount')
                      ->comment('Default commission percentage on sales for referring staff');
            }
            if (!Schema::hasColumn('business', 'default_extra_profit_commission_percent')) {
                $table->decimal('default_extra_profit_commission_percent', 5, 2)->default(0)->after('default_referral_commission_percent')
                      ->comment('Commission percentage from extra profit above predefined selling price');
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'referral_staff_user_id')) {
                $table->unsignedInteger('referral_staff_user_id')->nullable()->index()->after('commission_agent')
                      ->comment('User ID of the staff member who referred this sale');
            }
            if (!Schema::hasColumn('transactions', 'referral_code')) {
                $table->string('referral_code', 50)->nullable()->index()->after('referral_staff_user_id')
                      ->comment('Staff referral code entered during sale');
            }
            if (!Schema::hasColumn('transactions', 'referral_standard_commission')) {
                $table->decimal('referral_standard_commission', 22, 4)->default(0)->after('referral_code')
                      ->comment('Standard commission amount earned by referring staff');
            }
            if (!Schema::hasColumn('transactions', 'referral_extra_profit_commission')) {
                $table->decimal('referral_extra_profit_commission', 22, 4)->default(0)->after('referral_standard_commission')
                      ->comment('Extra profit commission amount earned by referring staff');
            }
            if (!Schema::hasColumn('transactions', 'referral_total_commission')) {
                $table->decimal('referral_total_commission', 22, 4)->default(0)->after('referral_extra_profit_commission')
                      ->comment('Grand total commission earned by referring staff');
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
        if (Schema::hasColumn('users', 'referral_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('referral_code');
            });
        }

        Schema::table('business', function (Blueprint $table) {
            if (Schema::hasColumn('business', 'default_referral_commission_percent')) {
                $table->dropColumn('default_referral_commission_percent');
            }
            if (Schema::hasColumn('business', 'default_extra_profit_commission_percent')) {
                $table->dropColumn('default_extra_profit_commission_percent');
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            $columns = [
                'referral_staff_user_id',
                'referral_code',
                'referral_standard_commission',
                'referral_extra_profit_commission',
                'referral_total_commission'
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
