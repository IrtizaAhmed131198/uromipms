<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper to safely add an index if it doesn't already exist.
     */
    private function addIndexSafely(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $existingIndexes = collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->all();

        if (!in_array($indexName, $existingIndexes)) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($columns, $indexName) {
                $tableBlueprint->index($columns, $indexName);
            });
        }
    }

    /**
     * Helper to safely drop an index if it exists.
     */
    private function dropIndexSafely(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $existingIndexes = collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->all();

        if (in_array($indexName, $existingIndexes)) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($indexName) {
                $tableBlueprint->dropIndex($indexName);
            });
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Transactions table - essential for Reports, Financial Statements, Dashboard, Sales & Purchases
        $this->addIndexSafely('transactions', ['business_id', 'type', 'status', 'transaction_date'], 'idx_trans_bus_type_status_date');
        $this->addIndexSafely('transactions', ['business_id', 'location_id', 'transaction_date'], 'idx_trans_bus_loc_date');
        $this->addIndexSafely('transactions', ['business_id', 'contact_id', 'transaction_date'], 'idx_trans_bus_contact_date');
        $this->addIndexSafely('transactions', ['business_id', 'created_by', 'transaction_date'], 'idx_trans_bus_user_date');

        // 2. Transaction Sell Lines table - essential for Profit/Loss, Sales Reports, Stock calculations
        $this->addIndexSafely('transaction_sell_lines', ['transaction_id', 'product_id'], 'idx_tsl_trans_prod');
        $this->addIndexSafely('transaction_sell_lines', ['transaction_id', 'variation_id'], 'idx_tsl_trans_var');
        $this->addIndexSafely('transaction_sell_lines', ['parent_sell_line_id'], 'idx_tsl_parent_sell_line_id');

        // 3. Purchase Lines table - essential for Stock Valuation, Inventory, Purchase Reports
        $this->addIndexSafely('purchase_lines', ['transaction_id', 'product_id'], 'idx_pl_trans_prod');
        $this->addIndexSafely('purchase_lines', ['product_id', 'variation_id'], 'idx_pl_prod_var');

        // 4. Transaction Sell Lines Purchase Lines table - essential for FIFO stock tracking & Profit/Loss
        $this->addIndexSafely('transaction_sell_lines_purchase_lines', ['purchase_line_id', 'sell_line_id'], 'idx_tslpl_purch_sell');

        // 5. Transaction Payments table - essential for Payment & Ledger Reports
        $this->addIndexSafely('transaction_payments', ['transaction_id', 'is_return', 'amount'], 'idx_tp_trans_return_amount');
        $this->addIndexSafely('transaction_payments', ['payment_for', 'paid_on'], 'idx_tp_payment_for_paid_on');
        $this->addIndexSafely('transaction_payments', ['business_id', 'paid_on'], 'idx_tp_bus_paid_on');

        // 6. Variation Location Details table - essential for POS screen product search & stock levels
        $this->addIndexSafely('variation_location_details', ['product_id', 'location_id'], 'idx_vld_prod_loc');
        $this->addIndexSafely('variation_location_details', ['variation_id', 'location_id'], 'idx_vld_var_loc');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropIndexSafely('transactions', 'idx_trans_bus_type_status_date');
        $this->dropIndexSafely('transactions', 'idx_trans_bus_loc_date');
        $this->dropIndexSafely('transactions', 'idx_trans_bus_contact_date');
        $this->dropIndexSafely('transactions', 'idx_trans_bus_user_date');

        $this->dropIndexSafely('transaction_sell_lines', 'idx_tsl_trans_prod');
        $this->dropIndexSafely('transaction_sell_lines', 'idx_tsl_trans_var');
        $this->dropIndexSafely('transaction_sell_lines', 'idx_tsl_parent_sell_line_id');

        $this->dropIndexSafely('purchase_lines', 'idx_pl_trans_prod');
        $this->dropIndexSafely('purchase_lines', 'idx_pl_prod_var');

        $this->dropIndexSafely('transaction_sell_lines_purchase_lines', 'idx_tslpl_purch_sell');

        $this->dropIndexSafely('transaction_payments', 'idx_tp_trans_return_amount');
        $this->dropIndexSafely('transaction_payments', 'idx_tp_payment_for_paid_on');
        $this->dropIndexSafely('transaction_payments', 'idx_tp_bus_paid_on');

        $this->dropIndexSafely('variation_location_details', 'idx_vld_prod_loc');
        $this->dropIndexSafely('variation_location_details', 'idx_vld_var_loc');
    }
};
