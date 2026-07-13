<?php

namespace App\Services;

use App\Services\SzamlazzHu\Dto\InvoiceData;

interface InvoiceServiceInterface
{
    public function createInvoice(InvoiceData $invoice): string;

}
