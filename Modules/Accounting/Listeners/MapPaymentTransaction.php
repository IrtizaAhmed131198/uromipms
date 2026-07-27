<?php

namespace Modules\Accounting\Listeners;

use App\BusinessLocation;
use App\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MapPaymentTransaction
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $payment = $event->transactionPayment;

        if (empty($payment) || empty($payment->transaction_id)) {
            return;
        }

        // ✅ FIRST: Handle delete case
        if (!empty($event->isDeleted) && $event->isDeleted === true) {
            $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();
            $accountingUtil->deleteMap(null, $payment->id);
            return;
        }

        // ✅ Only now fetch transaction
        $transaction = Transaction::find($payment->transaction_id);

        // Safety check
        if (empty($transaction)) {
            return;
        }

        if ($transaction->type == 'purchase') {
            $type = 'purchase_payment';
        } elseif ($transaction->type == 'sell') {
            $type = 'sell_payment';
        } else {
            return;
        }

        // Get location settings
        $business_location = BusinessLocation::find($transaction->location_id);

        if (empty($business_location) || empty($business_location->accounting_default_map)) {
            return;
        }

        $accounting_default_map = json_decode($business_location->accounting_default_map, true);

        $deposit_to = $accounting_default_map[$type]['deposit_to'] ?? null;
        $payment_account = $accounting_default_map[$type]['payment_account'] ?? null;

        // Do the mapping
        if (!is_null($deposit_to) && !is_null($payment_account)) {
            $payment_id = $payment->id;
            $user_id = request()->session()->get('user.id');
            $business_id = $transaction->business_id;

            $accountingUtil = new \Modules\Accounting\Utils\AccountingUtil();
            $accountingUtil->saveMap(
                $type,
                $payment_id,
                $user_id,
                $business_id,
                $deposit_to,
                $payment_account
            );
        }
    }
}
