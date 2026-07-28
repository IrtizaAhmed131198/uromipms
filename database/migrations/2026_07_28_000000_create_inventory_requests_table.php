<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('source_location_id'); // Location requested from
            $table->unsignedInteger('destination_location_id'); // Location requesting the stock
            $table->unsignedInteger('requested_by'); // User who made the request
            $table->unsignedInteger('approved_by')->nullable(); // Stock keeper who approved
            $table->unsignedInteger('accepted_by')->nullable(); // User who accepted receipt
            
            // Statuses: Draft, Pending Approval, Approved, Partially Approved, Rejected, Accepted, Completed
            $table->string('status')->default('Pending Approval'); 
            
            $table->text('notes')->nullable();
            $table->timestamps();

            // Note: UromiPMS typically uses unsignedInteger for these foreign keys
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('source_location_id')->references('id')->on('business_locations')->onDelete('cascade');
            $table->foreign('destination_location_id')->references('id')->on('business_locations')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('accepted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_requests');
    }
}
