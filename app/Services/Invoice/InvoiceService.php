<?php

namespace App\Services\Invoice;

use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
class InvoiceService
{
    /**
     * Compte le nombre de factures non payées dans la vue unpaid_invoices.
     *
     * @return int
     */
    public function getUnpaidInvoicesCount(): int
    {
        return DB::table('unpaid_invoices')->count();
    }

    public function getTotalAmountDue(): float
    {
        return Invoice::all()->sum(function ($invoice) {
            $calculator = new InvoiceCalculator($invoice);
            return $calculator->getTotalPrice()->getAmount();
        });
    }
}