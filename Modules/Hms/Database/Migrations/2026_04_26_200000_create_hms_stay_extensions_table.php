<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('hms_stay_extensions', function (Blueprint $table) {
            $table->id();
            $table->integer('transaction_id')->index();
            $table->dateTime('old_departure_datetime');
            $table->dateTime('new_departure_datetime');
            $table->integer('extra_nights')->default(1);
            $table->decimal('additional_charges', 22, 4)->default(0);
            $table->integer('extended_by');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('hms_stay_extensions');
    }
};
