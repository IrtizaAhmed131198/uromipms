<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLetterheadAndSignatureToBusinessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business', function (Blueprint $table) {
            if (!Schema::hasColumn('business', 'letterhead_image')) {
                $table->string('letterhead_image')->nullable()->after('logo')
                      ->comment('Company letterhead/header image path');
            }
            if (!Schema::hasColumn('business', 'footer_image')) {
                $table->string('footer_image')->nullable()->after('letterhead_image')
                      ->comment('Company footer image path');
            }
            if (!Schema::hasColumn('business', 'signature_image')) {
                $table->string('signature_image')->nullable()->after('footer_image')
                      ->comment('Authorized signature image path');
            }
            if (!Schema::hasColumn('business', 'quotation_terms')) {
                $table->text('quotation_terms')->nullable()->after('signature_image')
                      ->comment('Default terms and conditions for quotations');
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
        Schema::table('business', function (Blueprint $table) {
            $columns = ['letterhead_image', 'footer_image', 'signature_image', 'quotation_terms'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('business', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
