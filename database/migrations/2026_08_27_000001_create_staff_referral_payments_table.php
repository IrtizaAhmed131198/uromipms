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
        if (!Schema::hasTable('staff_referral_payments')) {
            Schema::create('staff_referral_payments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('business_id')->index();
                $table->unsignedInteger('user_id')->index()->comment('Staff member who received the bonus');
                $table->decimal('amount', 22, 4)->default(0);
                $table->dateTime('paid_on');
                $table->string('method', 50)->default('cash')->comment('Payment method: cash, bank_transfer, etc.');
                $table->unsignedInteger('account_id')->nullable()->index()->comment('Account if linked to accounts table');
                $table->string('payment_ref_no', 100)->nullable();
                $table->text('note')->nullable();
                $table->unsignedInteger('created_by')->index()->comment('Admin who recorded the payment');
                $table->timestamps();

                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('staff_referral_payments');
    }
};
