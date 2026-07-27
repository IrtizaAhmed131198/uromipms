<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hms_room_change_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('transaction_id');
            $table->integer('booking_line_id');
            $table->integer('from_room_id');
            $table->integer('to_room_id');
            $table->integer('from_room_type_id');
            $table->integer('to_room_type_id');
            $table->decimal('from_total_price', 22, 4)->default(0);
            $table->decimal('to_total_price', 22, 4)->default(0);
            $table->decimal('price_difference', 22, 4)->default(0);
            $table->integer('changed_by');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hms_room_change_logs');
    }
};
