<?php

namespace App\Services;

use App\Models\SalesInvoice;
use App\Models\SalesInvoicePayment;
use App\Services\SzamlazzHu\Dto\InvoiceData;

interface InvoiceServiceInterface
{
    public function createInvoice(InvoiceData $invoice): string;

    public function createInvoicePdf(InvoiceData $invoice, bool $preview = true): string;

    public function registerPayment(SalesInvoice $invoice, SalesInvoicePayment $payment, string $agentKey): void;

    public function deletePayment(SalesInvoice $invoice, SalesInvoicePayment $payment, string $agentKey): void;

}
