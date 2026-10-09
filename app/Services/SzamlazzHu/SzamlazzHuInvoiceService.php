<?php

namespace App\Services\SzamlazzHu;

use App\Models\SalesInvoice;
use App\Models\SalesInvoicePayment;
use App\Services\InvoiceServiceInterface;
use App\Services\SzamlazzHu\Dto\InvoiceData;
use App\Services\SzamlazzHu\Dto\ItemData;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use SzamlaAgent\Creditnote\InvoiceCreditNote;
use SzamlaAgent\Document\Document;
use SzamlaAgent\Header\InvoiceHeader;
use SzamlaAgent\SzamlaAgentAPI;
use SzamlaAgent\Buyer;
use SzamlaAgent\Document\Invoice\Invoice;
use SzamlaAgent\Document\Invoice\ReverseInvoice;
use SzamlaAgent\Item\InvoiceItem;
use SzamlaAgent\Log as SzamlazzLog;
use SzamlaAgent\Seller;
use SzamlaAgent\SzamlaAgentUtil;

class SzamlazzHuInvoiceService implements InvoiceServiceInterface
{
    private function applyInvoicePrefix(object $invoice, ?string $prefix): void
    {
        $prefix = is_string($prefix) ? trim($prefix) : '';
        if ($prefix === '') {
            return;
        }

        try {
            if (method_exists($invoice, 'getHeader')) {
                $header = $invoice->getHeader();
                if (is_object($header)) {
                    foreach (['setPrefix', 'setInvoicePrefix', 'setInvoiceNumberPrefix', 'setDocumentPrefix', 'setDocPrefix'] as $method) {
                        if (method_exists($header, $method)) {
                            $header->{$method}($prefix);
                            return;
                        }
                    }
                }
            }

            foreach (['setPrefix', 'setInvoicePrefix', 'setInvoiceNumberPrefix', 'setDocumentPrefix', 'setDocPrefix'] as $method) {
                if (method_exists($invoice, $method)) {
                    $invoice->{$method}($prefix);
                    return;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private function extractCorrectivedNumberFromResult(object $result): ?string
    {
        try {
            $request = null;
            if (method_exists($result, 'getRequest')) {
                $request = $result->getRequest();
            } elseif (property_exists($result, 'request')) {
                $request = $result->request;
            }

            if (!is_object($request)) {
                return null;
            }

            $entity = null;
            if (method_exists($request, 'getEntity')) {
                $entity = $request->getEntity();
            } elseif (property_exists($request, 'entity')) {
                $entity = $request->entity;
            }

            if (!is_object($entity)) {
                return null;
            }

            $header = null;
            if (method_exists($entity, 'getHeader')) {
                $header = $entity->getHeader();
            } elseif (property_exists($entity, 'header')) {
                $header = $entity->header;
            }

            if (!is_object($header)) {
                return null;
            }

            if (method_exists($header, 'getCorrectivedNumber')) {
                $v = trim((string) $header->getCorrectivedNumber());
                return $v !== '' ? $v : null;
            }

            $ref = new \ReflectionClass($header);
            if ($ref->hasProperty('correctivedNumber')) {
                $p = $ref->getProperty('correctivedNumber');
                $p->setAccessible(true);
                $v = $p->getValue($header);
                $v = trim((string) $v);
                return $v !== '' ? $v : null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function extractInvoiceNumberFromResult(object $result): ?string
    {
        try {
            if (method_exists($result, 'getInvoiceNumber')) {
                $n = (string) $result->getInvoiceNumber();
                return trim($n) !== '' ? trim($n) : null;
            }

            if (method_exists($result, 'getDocumentNumber')) {
                $n = (string) $result->getDocumentNumber();
                return trim($n) !== '' ? trim($n) : null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    public function createCorrectiveInvoicePdfWithNumber(InvoiceData $data, string $originalInvoiceNumber, bool $preview = true): array
    {
        $apiKey = trim((string) ($data->agentKey ?? ''));
        if ($apiKey === '') {
            throw new \RuntimeException('Hiányzik a számlázó API kulcs (cég szinten).');
        }

        $originalInvoiceNumber = trim((string) $originalInvoiceNumber);
        if ($originalInvoiceNumber === '') {
            throw new \RuntimeException('Hiányzik az eredeti számlaszám a helyesbítő számlához.');
        }

        if (method_exists(SzamlaAgentAPI::class, 'create')) {
            try {
                $agent = SzamlaAgentAPI::create($apiKey, true, SzamlazzLog::LOG_LEVEL_OFF);
            } catch (\Throwable $e) {
                $agent = SzamlaAgentAPI::create($apiKey);
            }
        } else {
            $agent = new SzamlaAgentAPI($apiKey);
        }

        $pdfDirRel = 'szamlazzhu/pdf';
        $beforeTs = time();

        $this->prepareAgentDirs($agent);

        $buyer = new Buyer(
            $data->customer->name,
            $data->customer->zip,
            $data->customer->city,
            $data->customer->address
        );

        if ($data->customer->taxNumber) {
            $buyer->setTaxNumber(
                $data->customer->taxNumber
            );
        }

        $invoiceClass = '\\SzamlaAgent\\Document\\Invoice\\CorrectiveInvoice';
        if (class_exists($invoiceClass)) {
            $invoice = new $invoiceClass(Invoice::INVOICE_TYPE_P_INVOICE);
        } else {
            $invoice = new Invoice(Invoice::INVOICE_TYPE_P_INVOICE);
        }

        $invoice->setBuyer($buyer);

        $this->applyInvoicePrefix($invoice, $data->invoicePrefix ?? null);

        $header = $invoice->getHeader();

        $header->setCorrectivedNumber($originalInvoiceNumber);
        $header->setCorrective(true);

        $invoice->setHeader($header);

        $headerComment = isset($data->noteForDocument) ? trim((string) $data->noteForDocument) : '';
        if ($headerComment !== '') {
            try {
                if ($header && method_exists($header, 'setComment')) {
                    $header->setComment($headerComment);

                    if (method_exists($invoice, 'setHeader')) {
                        $invoice->setHeader($header);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if ($preview) {
            try {
                if ($header && method_exists($header, 'setPreviewPdf')) {
                    $header->setPreviewPdf(true);

                    if (method_exists($invoice, 'setHeader')) {
                        $invoice->setHeader($header);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }


        $correctionToPay = 0.0;
        foreach ($data->items as $itemData) {
            if (!$itemData instanceof ItemData) {
                continue;
            }

            $item = new InvoiceItem($itemData->name, $itemData->unitPrice);
            $item->setQuantity($itemData->quantity);
            if (method_exists($item, 'setQuantityUnit')) {
                $item->setQuantityUnit($itemData->unit);
            } elseif (method_exists($item, 'setUnit')) {
                $item->setUnit($itemData->unit);
            }

            $netUnitPrice = (float) $itemData->unitPrice;
            $netPrice = $netUnitPrice * (float) $itemData->quantity;
            $vatPercent = (float) $itemData->vatPercent;
            $vatAmount = $netPrice * ($vatPercent / 100);
            $grossAmount = $netPrice + $vatAmount;
            $correctionToPay += $grossAmount;

            if (method_exists($item, 'setNetUnitPrice')) {
                $item->setNetUnitPrice($netUnitPrice);
            }
            if (method_exists($item, 'setNetPrice')) {
                $item->setNetPrice($netPrice);
            }
            if (method_exists($item, 'setVatAmount')) {
                $item->setVatAmount($vatAmount);
            }
            if (method_exists($item, 'setGrossAmount')) {
                $item->setGrossAmount($grossAmount);
            }

            if (method_exists($item, 'setVat')) {
                $item->setVat((string) $itemData->vatPercent);
            } elseif (method_exists($item, 'setVatPercent')) {
                $item->setVatPercent((string) $itemData->vatPercent);
            }

            $invoice->addItem($item);
        }

        if ($header && method_exists($header, 'setCorrectionToPay')) {
            $header->setCorrectionToPay($correctionToPay);

            if (method_exists($invoice, 'setHeader')) {
                $invoice->setHeader($header);
            }
        }

        $result = null;
        foreach (['generateCorrectiveInvoice', 'generateCorrectionInvoice', 'generateCorrective', 'generateCorrection'] as $method) {
            if (method_exists($agent, $method)) {
                $result = $agent->{$method}($invoice);
                break;
            }
        }

        if (!$result) {
            $result = $agent->generateInvoice($invoice);
        }

        if (!$result->isSuccess()) {
            throw new \RuntimeException(
                $result->getErrorMessage()
            );
        }
        $header = $invoice->getHeader();

        $invoiceNumber = $this->extractInvoiceNumberFromResult($result);

        try {
            foreach (['getPdfFile', 'getPdf', 'getPdfData', 'getPdfContent', 'getPDF'] as $method) {
                if (method_exists($result, $method)) {
                    $pdf = $result->{$method}();
                    if (is_string($pdf) && $pdf !== '') {
                        return [
                            'pdf' => $pdf,
                            'invoice_number' => $invoiceNumber,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            $files = Storage::disk('local')->files($pdfDirRel);
            $candidates = collect($files)
                ->filter(fn($f) => str_ends_with(strtolower($f), '.pdf'))
                ->map(function ($f) {
                    return [
                        'file' => $f,
                        'ts' => Storage::disk('local')->lastModified($f),
                    ];
                })
                ->sortByDesc('ts')
                ->values();

            $selected = $candidates->first(function ($row) use ($beforeTs) {
                return (int) ($row['ts'] ?? 0) >= ($beforeTs - 2);
            }) ?? $candidates->first();

            if (!$selected || empty($selected['file'])) {
                throw new \RuntimeException('Számlázz.hu PDF nem található a generálás után.');
            }

            return [
                'pdf' => (string) Storage::disk('local')->get($selected['file']),
                'invoice_number' => $invoiceNumber,
                'correctived_number' => $originalInvoiceNumber,
            ];
        } catch (\Throwable $e) {
            throw new \RuntimeException('Számlázz.hu PDF beolvasása sikertelen: ' . $e->getMessage());
        }
    }

    private function prepareAgentDirs(SzamlaAgentAPI $agent): void
    {
        try {
            $baseDir = Storage::disk('local')->path('szamlazzhu');
            @mkdir($baseDir . DIRECTORY_SEPARATOR . 'xmls', 0775, true);
            @mkdir($baseDir . DIRECTORY_SEPARATOR . 'logs', 0775, true);
            @mkdir($baseDir . DIRECTORY_SEPARATOR . 'pdf', 0775, true);

            if (method_exists($agent, 'setXmlDirName')) {
                $agent->setXmlDirName($baseDir . DIRECTORY_SEPARATOR . 'xmls');
            }
            if (method_exists($agent, 'setLogDirName')) {
                $agent->setLogDirName($baseDir . DIRECTORY_SEPARATOR . 'logs');
            }
            if (method_exists($agent, 'setPdfDirName')) {
                $agent->setPdfDirName($baseDir . DIRECTORY_SEPARATOR . 'pdf');
            }
            if (method_exists($agent, 'setPDFDirName')) {
                $agent->setPDFDirName($baseDir . DIRECTORY_SEPARATOR . 'pdf');
            }

            if (method_exists($agent, 'setXmlFileSave')) {
                $agent->setXmlFileSave(false);
            }
            if (method_exists($agent, 'setRequestXmlFileSave')) {
                $agent->setRequestXmlFileSave(false);
            }
            if (method_exists($agent, 'setResponseXmlFileSave')) {
                $agent->setResponseXmlFileSave(false);
            }

            // Preview should not rely on file system writes.
            if (method_exists($agent, 'setPdfFileSave')) {
                $agent->setPdfFileSave(false);
            }

            if (method_exists($agent, 'setDownloadPdf')) {
                $agent->setDownloadPdf(true);
            }

            $vendorBase = base_path('vendor/kboss/szamlaagent_v2');
            @mkdir($vendorBase . DIRECTORY_SEPARATOR . 'xmls', 0775, true);
            @mkdir($vendorBase . DIRECTORY_SEPARATOR . 'logs', 0775, true);
            @mkdir($vendorBase . DIRECTORY_SEPARATOR . 'pdf', 0775, true);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function createInvoice(InvoiceData $data): string
    {
        $apiKey = trim((string) ($data->agentKey ?? ''));
        if ($apiKey === '') {
            throw new \RuntimeException('Hiányzik a számlázó API kulcs (cég szinten).');
        }

        // Prefer silent logging for API calls from web requests.
        // Signature differs across package versions, so we keep it defensive.
        if (method_exists(SzamlaAgentAPI::class, 'create')) {
            try {
                $agent = SzamlaAgentAPI::create($apiKey, true, SzamlazzLog::LOG_LEVEL_OFF);
            } catch (\Throwable $e) {
                $agent = SzamlaAgentAPI::create($apiKey);
            }
        } else {
            $agent = new SzamlaAgentAPI($apiKey);
        }

        $this->prepareAgentDirs($agent);

        $buyer = new Buyer(
            $data->customer->name,
            $data->customer->zip,
            $data->customer->city,
            $data->customer->address
        );

        if ($data->customer->taxNumber) {
            $buyer->setTaxNumber(
                $data->customer->taxNumber
            );
        }

        $invoice = new Invoice(Invoice::INVOICE_TYPE_P_INVOICE);
        $invoice->setBuyer($buyer);
        //$invoice->getHeader()->setPrefix('JB');
        $this->applyInvoicePrefix($invoice, $data->invoicePrefix ?? null);

        $headerComment = isset($data->noteForDocument) ? trim((string) $data->noteForDocument) : '';
        if ($headerComment !== '') {
            try {
                if (method_exists($invoice, 'getHeader')) {
                    $header = $invoice->getHeader();
                    if ($header && method_exists($header, 'setComment')) {
                        $header->setComment($headerComment);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        foreach ($data->items as $itemData) {
            if (!$itemData instanceof ItemData) {
                continue;
            }

            $item = new InvoiceItem($itemData->name, $itemData->unitPrice);
            $item->setQuantity($itemData->quantity);
            if (method_exists($item, 'setQuantityUnit')) {
                $item->setQuantityUnit($itemData->unit);
            } elseif (method_exists($item, 'setUnit')) {
                $item->setUnit($itemData->unit);
            }

            // Some API versions require explicit net/gross/vat values.
            $netUnitPrice = (float) $itemData->unitPrice;
            $netPrice = $netUnitPrice * (float) $itemData->quantity;
            $vatPercent = (float) $itemData->vatPercent;
            $vatAmount = $netPrice * ($vatPercent / 100);
            $grossAmount = $netPrice + $vatAmount;

            if (method_exists($item, 'setNetUnitPrice')) {
                $item->setNetUnitPrice($netUnitPrice);
            }
            if (method_exists($item, 'setNetPrice')) {
                $item->setNetPrice($netPrice);
            }
            if (method_exists($item, 'setVatAmount')) {
                $item->setVatAmount($vatAmount);
            }
            if (method_exists($item, 'setGrossAmount')) {
                $item->setGrossAmount($grossAmount);
            }

            // VAT setter differs by package version.
            if (method_exists($item, 'setVat')) {
                $item->setVat((string) $itemData->vatPercent);
            } elseif (method_exists($item, 'setVatPercent')) {
                $item->setVatPercent((string) $itemData->vatPercent);
            }

            $invoice->addItem($item);
        }

        //SzamlaAgentUtil::setBasePath(storage_path('app/private/szamlazzhu'));
        //$agent->setXmlFileSave(true);
        //$agent->setRequestXmlFileSave(true);

        $result = $agent->generateInvoice($invoice);

        if (!$result->isSuccess()) {
            throw new \RuntimeException(
                $result->getErrorMessage()
            );
        }

        if (method_exists($result, 'getInvoiceNumber')) {
            return (string) $result->getInvoiceNumber();
        }

        if (method_exists($result, 'getDocumentNumber')) {
            return (string) $result->getDocumentNumber();
        }

        throw new \RuntimeException('Számlázz.hu válaszból nem olvasható ki a bizonylatszám.');
    }

    public function createInvoicePdf(InvoiceData $data, bool $preview = true): string
    {
        $apiKey = trim((string) ($data->agentKey ?? ''));
        if ($apiKey === '') {
            throw new \RuntimeException('Hiányzik a számlázó API kulcs (cég szinten).');
        }

        if (method_exists(SzamlaAgentAPI::class, 'create')) {
            try {
                $agent = SzamlaAgentAPI::create($apiKey, true, SzamlazzLog::LOG_LEVEL_OFF);
            } catch (\Throwable $e) {
                $agent = SzamlaAgentAPI::create($apiKey);
            }
        } else {
            $agent = new SzamlaAgentAPI($apiKey);
        }

        $pdfDirRel = 'szamlazzhu/pdf';
        $beforeTs = time();

        $this->prepareAgentDirs($agent);

        $buyer = new Buyer(
            $data->customer->name,
            $data->customer->zip,
            $data->customer->city,
            $data->customer->address
        );

        if ($data->customer->taxNumber) {
            $buyer->setTaxNumber(
                $data->customer->taxNumber
            );
        }

        $invoice = new Invoice(Invoice::INVOICE_TYPE_P_INVOICE);
        $invoice->setBuyer($buyer);

        $this->applyInvoicePrefix($invoice, $data->invoicePrefix ?? null);

        $headerComment = isset($data->noteForDocument) ? trim((string) $data->noteForDocument) : '';
        if ($headerComment !== '') {
            try {
                if (method_exists($invoice, 'getHeader')) {
                    $header = $invoice->getHeader();
                    if ($header && method_exists($header, 'setComment')) {
                        $header->setComment($headerComment);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if ($preview) {
            try {
                if (method_exists($invoice, 'getHeader')) {
                    $header = $invoice->getHeader();
                    if ($header && method_exists($header, 'setPreviewPdf')) {
                        $header->setPreviewPdf(true);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        foreach ($data->items as $itemData) {
            if (!$itemData instanceof ItemData) {
                continue;
            }

            $item = new InvoiceItem($itemData->name, $itemData->unitPrice);
            $item->setQuantity($itemData->quantity);
            if (method_exists($item, 'setQuantityUnit')) {
                $item->setQuantityUnit($itemData->unit);
            } elseif (method_exists($item, 'setUnit')) {
                $item->setUnit($itemData->unit);
            }

            $netUnitPrice = (float) $itemData->unitPrice;
            $netPrice = $netUnitPrice * (float) $itemData->quantity;
            $vatPercent = (float) $itemData->vatPercent;
            $vatAmount = $netPrice * ($vatPercent / 100);
            $grossAmount = $netPrice + $vatAmount;

            if (method_exists($item, 'setNetUnitPrice')) {
                $item->setNetUnitPrice($netUnitPrice);
            }
            if (method_exists($item, 'setNetPrice')) {
                $item->setNetPrice($netPrice);
            }
            if (method_exists($item, 'setVatAmount')) {
                $item->setVatAmount($vatAmount);
            }
            if (method_exists($item, 'setGrossAmount')) {
                $item->setGrossAmount($grossAmount);
            }

            if (method_exists($item, 'setVat')) {
                $item->setVat((string) $itemData->vatPercent);
            } elseif (method_exists($item, 'setVatPercent')) {
                $item->setVatPercent((string) $itemData->vatPercent);
            }

            $invoice->addItem($item);
        }
        /*SzamlaAgentUtil::setBasePath(storage_path('app/private/szamlazzhu'));
        $agent->setXmlFileSave(true);
        $agent->setRequestXmlFileSave(true);*/

        $result = $agent->generateInvoice($invoice);

        if (!$result->isSuccess()) {
            throw new \RuntimeException(
                $result->getErrorMessage()
            );
        }

        // Prefer PDF bytes from response (when file saving is disabled).
        try {
            foreach (['getPdfFile', 'getPdf', 'getPdfData', 'getPdfContent', 'getPDF'] as $method) {
                if (method_exists($result, $method)) {
                    $pdf = $result->{$method}();
                    if (is_string($pdf) && $pdf !== '') {
                        return $pdf;
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            $files = Storage::disk('local')->files($pdfDirRel);
            $candidates = collect($files)
                ->filter(fn($f) => str_ends_with(strtolower($f), '.pdf'))
                ->map(function ($f) {
                    return [
                        'file' => $f,
                        'ts' => Storage::disk('local')->lastModified($f),
                    ];
                })
                ->sortByDesc('ts')
                ->values();

            $selected = $candidates->first(function ($row) use ($beforeTs) {
                return (int) ($row['ts'] ?? 0) >= ($beforeTs - 2);
            }) ?? $candidates->first();

            if (!$selected || empty($selected['file'])) {
                throw new \RuntimeException('Számlázz.hu PDF nem található a generálás után.');
            }

            return (string) Storage::disk('local')->get($selected['file']);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Számlázz.hu PDF beolvasása sikertelen: ' . $e->getMessage());
        }
    }

    public function createInvoicePdfWithNumber(InvoiceData $data, bool $preview = true): array
    {
        $apiKey = trim((string) ($data->agentKey ?? ''));
        if ($apiKey === '') {
            throw new \RuntimeException('Hiányzik a számlázó API kulcs (cég szinten).');
        }

        if (method_exists(SzamlaAgentAPI::class, 'create')) {
            try {
                $agent = SzamlaAgentAPI::create($apiKey, true, SzamlazzLog::LOG_LEVEL_OFF);
            } catch (\Throwable $e) {
                $agent = SzamlaAgentAPI::create($apiKey);
            }
        } else {
            $agent = new SzamlaAgentAPI($apiKey);
        }

        $pdfDirRel = 'szamlazzhu/pdf';
        $beforeTs = time();

        $this->prepareAgentDirs($agent);

        $buyer = new Buyer(
            $data->customer->name,
            $data->customer->zip,
            $data->customer->city,
            $data->customer->address,
            $data->customer->email
        );

        if ($data->customer->taxNumber) {
            $buyer->setTaxNumber(
                $data->customer->taxNumber
            );
        }

        if ($data->customer->sendEmail) {
            $buyer->setEmail($data->customer->email);
            $buyer->setSendEmail(true);
        }

        $invoice = new Invoice(Invoice::INVOICE_TYPE_P_INVOICE);
        $invoice->setBuyer($buyer);

        $this->applyInvoicePrefix($invoice, $data->invoicePrefix ?? null);

        $headerComment = isset($data->noteForDocument) ? trim((string) $data->noteForDocument) : '';
        if ($headerComment !== '') {
            try {
                if (method_exists($invoice, 'getHeader')) {
                    $header = $invoice->getHeader();
                    if ($header && method_exists($header, 'setComment')) {
                        $header->setComment($headerComment);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if ($preview) {
            try {
                if (method_exists($invoice, 'getHeader')) {
                    $header = $invoice->getHeader();
                    if ($header && method_exists($header, 'setPreviewPdf')) {
                        $header->setPreviewPdf(true);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        foreach ($data->items as $itemData) {
            if (!$itemData instanceof ItemData) {
                continue;
            }

            $item = new InvoiceItem($itemData->name, $itemData->unitPrice);
            $item->setQuantity($itemData->quantity);
            if (method_exists($item, 'setQuantityUnit')) {
                $item->setQuantityUnit($itemData->unit);
            } elseif (method_exists($item, 'setUnit')) {
                $item->setUnit($itemData->unit);
            }

            $netUnitPrice = (float) $itemData->unitPrice;
            $netPrice = $netUnitPrice * (float) $itemData->quantity;
            $vatPercent = (float) $itemData->vatPercent;
            $vatAmount = $netPrice * ($vatPercent / 100);
            $grossAmount = $netPrice + $vatAmount;

            if (method_exists($item, 'setNetUnitPrice')) {
                $item->setNetUnitPrice($netUnitPrice);
            }
            if (method_exists($item, 'setNetPrice')) {
                $item->setNetPrice($netPrice);
            }
            if (method_exists($item, 'setVatAmount')) {
                $item->setVatAmount($vatAmount);
            }
            if (method_exists($item, 'setGrossAmount')) {
                $item->setGrossAmount($grossAmount);
            }

            if (method_exists($item, 'setVat')) {
                $item->setVat((string) $itemData->vatPercent);
            } elseif (method_exists($item, 'setVatPercent')) {
                $item->setVatPercent((string) $itemData->vatPercent);
            }

            $invoice->addItem($item);
        }

        /*SzamlaAgentUtil::setBasePath(storage_path('app/private/szamlazzhu'));
        $agent->setXmlFileSave(true);
        $agent->setRequestXmlFileSave(true);*/

        $result = $agent->generateInvoice($invoice);

        if (!$result->isSuccess()) {
            throw new \RuntimeException(
                $result->getErrorMessage()
            );
        }

        $invoiceNumber = $this->extractInvoiceNumberFromResult($result);

        try {
            foreach (['getPdfFile', 'getPdf', 'getPdfData', 'getPdfContent', 'getPDF'] as $method) {
                if (method_exists($result, $method)) {
                    $pdf = $result->{$method}();
                    if (is_string($pdf) && $pdf !== '') {
                        return [
                            'pdf' => $pdf,
                            'invoice_number' => $invoiceNumber,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            $files = Storage::disk('local')->files($pdfDirRel);
            $candidates = collect($files)
                ->filter(fn($f) => str_ends_with(strtolower($f), '.pdf'))
                ->map(function ($f) {
                    return [
                        'file' => $f,
                        'ts' => Storage::disk('local')->lastModified($f),
                    ];
                })
                ->sortByDesc('ts')
                ->values();

            $selected = $candidates->first(function ($row) use ($beforeTs) {
                return (int) ($row['ts'] ?? 0) >= ($beforeTs - 2);
            }) ?? $candidates->first();

            if (!$selected || empty($selected['file'])) {
                throw new \RuntimeException('Számlázz.hu PDF nem található a generálás után.');
            }

            return [
                'pdf' => (string) Storage::disk('local')->get($selected['file']),
                'invoice_number' => $invoiceNumber,
            ];
        } catch (\Throwable $e) {
            throw new \RuntimeException('Számlázz.hu PDF beolvasása sikertelen: ' . $e->getMessage());
        }
    }

    public function createReverseInvoicePdfWithNumber(string $invoiceNumber, string $agentKey): array
    {
        $apiKey = trim((string) $agentKey);
        if ($apiKey === '') {
            throw new \RuntimeException('Hiányzik a számlázó API kulcs (cég szinten).');
        }

        $invoiceNumber = trim((string) $invoiceNumber);
        if ($invoiceNumber === '') {
            throw new \RuntimeException('Hiányzik az eredeti számlaszám a sztornózáshoz.');
        }

        if (method_exists(SzamlaAgentAPI::class, 'create')) {
            try {
                $agent = SzamlaAgentAPI::create($apiKey, true, SzamlazzLog::LOG_LEVEL_OFF);
            } catch (\Throwable $e) {
                $agent = SzamlaAgentAPI::create($apiKey);
            }
        } else {
            $agent = new SzamlaAgentAPI($apiKey);
        }

        $beforeTs = time();
        $pdfDirRel = 'szamlazzhu/pdf';

        $this->prepareAgentDirs($agent);

        $reverse = new ReverseInvoice();
        try {
            if (method_exists($reverse, 'getHeader')) {
                $header = $reverse->getHeader();
                if ($header && method_exists($header, 'setInvoiceNumber')) {
                    $header->setInvoiceNumber($invoiceNumber);
                }
                if ($header && method_exists($header, 'setOriginalInvoiceNumber')) {
                    $header->setOriginalInvoiceNumber($invoiceNumber);
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $result = null;
        foreach (['generateReverseInvoice', 'generateStornoInvoice', 'generateReverse', 'generateStorno'] as $method) {
            if (method_exists($agent, $method)) {
                $result = $agent->{$method}($reverse);
                break;
            }
        }

        if (!$result) {
            throw new \RuntimeException('Számlázz.hu reverse/stornó API metódus nem elérhető a használt SDK verzióban.');
        }

        if (method_exists($result, 'isSuccess') && !$result->isSuccess()) {
            $msg = method_exists($result, 'getErrorMessage') ? (string) $result->getErrorMessage() : 'Sztornó sikertelen.';
            throw new \RuntimeException($msg);
        }

        $stornoNumber = $this->extractInvoiceNumberFromResult($result);

        try {
            foreach (['getPdfFile', 'getPdf', 'getPdfData', 'getPdfContent', 'getPDF'] as $method) {
                if (method_exists($result, $method)) {
                    $pdf = $result->{$method}();
                    if (is_string($pdf) && $pdf !== '') {
                        return [
                            'pdf' => $pdf,
                            'invoice_number' => $stornoNumber,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            $files = Storage::disk('local')->files($pdfDirRel);
            $candidates = collect($files)
                ->filter(fn($f) => str_ends_with(strtolower($f), '.pdf'))
                ->map(function ($f) {
                    return [
                        'file' => $f,
                        'ts' => Storage::disk('local')->lastModified($f),
                    ];
                })
                ->sortByDesc('ts')
                ->values();

            $selected = $candidates->first(function ($row) use ($beforeTs) {
                return (int) ($row['ts'] ?? 0) >= ($beforeTs - 2);
            }) ?? $candidates->first();

            if (!$selected || empty($selected['file'])) {
                throw new \RuntimeException('Számlázz.hu sztornó PDF nem található a generálás után.');
            }

            return [
                'pdf' => (string) Storage::disk('local')->get($selected['file']),
                'invoice_number' => $stornoNumber,
            ];
        } catch (\Throwable $e) {
            throw new \RuntimeException('Számlázz.hu sztornó PDF beolvasása sikertelen: ' . $e->getMessage());
        }
    }

    public function registerPayment(SalesInvoice $invoice, SalesInvoicePayment $payment, string $agentKey): void
    {
        $apiKey = trim($agentKey);

        if ($apiKey === '') {
            throw new \RuntimeException(
                'Hiányzik a számlázó API kulcs (cég szinten).'
            );
        }

        if (empty($invoice->invoice_number)) {
            throw new \RuntimeException(
                'A számlához nem tartozik számlaszám.'
            );
        }

        if (!$payment->paid_at || (float) $payment->amount <= 0) {
            throw new \RuntimeException(
                'A befizetés dátuma vagy összege érvénytelen.'
            );
        }

        $agent = SzamlaAgentAPI::create(
            $apiKey,
            true,
            \SzamlaAgent\Log::LOG_LEVEL_OFF
        );

        $this->prepareAgentDirs($agent);

        $szamla = new Invoice(Invoice::INVOICE_TYPE_E_INVOICE);

        $header = new InvoiceHeader();
        $header->setInvoiceNumber($invoice->invoice_number);

        $szamla->setHeader($header);

        $creditNote = new InvoiceCreditNote(
            $payment->paid_at->format('Y-m-d'),
            (float) $payment->amount,
            $this->getSzamlazzPaymentMethod($payment->payment_method),
            $payment->note ?? ''
        );

        $szamla->addCreditNote($creditNote);

        $result = $agent->payInvoice($szamla);

        if (!$result) {
            throw new \RuntimeException(
                'A befizetés rögzítése sikertelen a Számlázz.hu rendszerében.'
            );
        }
    }

    public function deletePayment(SalesInvoice $invoice, SalesInvoicePayment $payment, string $agentKey): void
    {
        $apiKey = trim((string) ($data->agentKey ?? ''));
        if ($apiKey === '') {
            throw new \RuntimeException('Hiányzik a számlázó API kulcs (cég szinten).');
        }
    }

    private function getSzamlazzPaymentMethod(?string $paymentMethod): string
    {
        return match (mb_strtolower(trim((string) $paymentMethod))) {
            'készpénz', 'cash' => Document::PAYMENT_METHOD_CASH,
            'bankkártya', 'bankkartya', 'bankcard' => Document::PAYMENT_METHOD_BANKCARD,
            'utánvét', 'utanvet', 'cod' => Document::PAYMENT_METHOD_COD,
            'átutalás', 'atutalas', 'transfer', '' => Document::PAYMENT_METHOD_TRANSFER,
            default => Document::PAYMENT_METHOD_TRANSFER,
        };
    }

}
