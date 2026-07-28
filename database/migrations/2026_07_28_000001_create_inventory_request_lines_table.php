<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryRequestLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_request_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_request_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('variation_id'); // If using product variations
            
            $table->decimal('quantity_requested', 22, 4);
            $table->decimal('quantity_approved', 22, 4)->default(0);
            $table->decimal('quantity_received', 22, 4)->default(0);
            
            $table->timestamps();

            $table->foreign('inventory_request_id')->references('id')->on('inventory_requests')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('variation_id')->references('id')->on('variations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_request_lines');
    }
}
