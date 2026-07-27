<?php

namespace App\Console\Commands;

use App\Business;
use App\BusinessLocation;
use App\Utils\ProductUtil;
use Illuminate\Console\Command;

class RecalculateStock extends Command
{
    protected $signature = 'pos:recalculateStock
                            {--business_id= : Limit to a single business ID}
                            {--dry-run : Show mismatches without fixing them}';

    protected $description = 'Recalculate qty_available in variation_location_details from transaction history and fix any mismatches.';

    protected $productUtil;

    public function __construct(ProductUtil $productUtil)
    {
        parent::__construct();
        $this->productUtil = $productUtil;
    }

    public function handle()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $dryRun     = $this->option('dry-run');
        $businessId = $this->option('business_id');

        $query = Business::query();
        if ($businessId) {
            $query->where('id', $businessId);
        }
        $businesses = $query->get();

        if ($businesses->isEmpty()) {
            $this->error('No businesses found.');
            return 1;
        }

        $totalFixed    = 0;
        $totalMismatch = 0;

        foreach ($businesses as $business) {
            $this->info("Business: [{$business->id}] {$business->name}");

            $locations = BusinessLocation::where('business_id', $business->id)->get();

            if ($locations->isEmpty()) {
                $this->line('  No locations found, skipping.');
                continue;
            }

            foreach ($locations as $location) {
                $this->line("  Location: [{$location->id}] {$location->name}");

                $rows = $this->productUtil->getVariationStockMisMatch(
                    $business->id,
                    null,
                    $location->id
                );

                if ($rows->isEmpty()) {
                    $this->line('    No stock records found.');
                    continue;
                }

                $bar = $this->output->createProgressBar($rows->count());
                $bar->start();

                foreach ($rows as $row) {
                    if (! $row->enable_stock) {
                        $bar->advance();
                        continue;
                    }

                    $current    = round((float) $row->stock, 4);
                    $calculated = round((float) $row->total_stock_calculated, 4);

                    if ($current !== $calculated) {
                        $totalMismatch++;

                        if (! $dryRun) {
                            $this->productUtil->fixVariationStockMisMatch(
                                $business->id,
                                $row->variation_id,
                                $location->id,
                                $calculated
                            );
                            $totalFixed++;
                        }

                        $this->newLine();
                        $label = $row->product . ($row->variation_name !== 'DUMMY' ? ' - ' . $row->variation_name : '');
                        $action = $dryRun ? 'MISMATCH' : 'FIXED';
                        $this->line("    [{$action}] {$label} (SKU: {$row->sub_sku}): {$current} → {$calculated}");
                    }

                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();
            }
        }

        $this->newLine();
        if ($dryRun) {
            $this->warn("Dry run — {$totalMismatch} mismatches found. Run without --dry-run to fix them.");
        } else {
            $this->info("Done — {$totalFixed} variation/location records corrected.");
        }

        return 0;
    }
}
