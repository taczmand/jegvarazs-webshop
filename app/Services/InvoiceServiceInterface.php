<?php

namespace App\Services;

use App\Services\SzamlazzHu\Dto\InvoiceData;

interface InvoiceServiceInterface
{
    public function createInvoice(InvoiceData $invoice): string;

    public function createInvoicePdf(InvoiceData $invoice, bool $preview = true): string;

}
