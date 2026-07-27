<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterHmsRoomChangeLogsAddColumns extends Migration
{
    public function up()
    {
        // Use raw SHOW TABLES — reliable on all MySQL/cPanel hosting setups
        $tableExists = count(DB::select("SHOW TABLES LIKE 'hms_room_change_logs'")) > 0;

        if (!$tableExists) {
            Schema::create('hms_room_change_logs', function (Blueprint $table) {
                $table->id();
                $table->integer('transaction_id')->index();
                $table->integer('booking_line_id');
                $table->integer('from_room_id');
                $table->integer('from_room_type_id');
                $table->integer('to_room_id');
                $table->integer('to_room_type_id');
                $table->integer('nights_stayed')->default(0);
                $table->integer('nights_remaining')->default(1);
                $table->decimal('old_price_per_night', 22, 4)->default(0);
                $table->decimal('new_price_per_night', 22, 4)->default(0);
                $table->decimal('price_difference', 22, 4)->default(0);
                $table->integer('changed_by');
                $table->text('note')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('hms_room_change_logs', function (Blueprint $table) {
                $cols = array_column(DB::select("SHOW COLUMNS FROM hms_room_change_logs"), 'Field');
                if (!in_array('nights_stayed', $cols)) {
                    $table->integer('nights_stayed')->default(0)->after('to_room_type_id');
                }
                if (!in_array('nights_remaining', $cols)) {
                    $table->integer('nights_remaining')->default(1)->after('nights_stayed');
                }
                if (!in_array('old_price_per_night', $cols)) {
                    $table->decimal('old_price_per_night', 22, 4)->default(0)->after('nights_remaining');
                }
                if (!in_array('new_price_per_night', $cols)) {
                    $table->decimal('new_price_per_night', 22, 4)->default(0)->after('old_price_per_night');
                }
                if (!in_array('note', $cols)) {
                    $table->text('note')->nullable()->after('changed_by');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('hms_room_change_logs');
    }
}
