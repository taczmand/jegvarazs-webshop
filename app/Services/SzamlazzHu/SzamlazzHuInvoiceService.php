<?php

namespace App\Services\SzamlazzHu;

use App\Services\InvoiceServiceInterface;
use App\Services\SzamlazzHu\Dto\InvoiceData;
use App\Services\SzamlazzHu\Dto\ItemData;
use Illuminate\Support\Facades\Storage;
use SzamlaAgent\SzamlaAgentAPI;
use SzamlaAgent\Buyer;
use SzamlaAgent\Document\Invoice\Invoice;
use SzamlaAgent\Item\InvoiceItem;
use SzamlaAgent\Log;
use SzamlaAgent\Seller;

class SzamlazzHuInvoiceService implements InvoiceServiceInterface
{
    public function createInvoice(InvoiceData $data): string
    {
        $apiKey = (string) config('szamlazzhu.agent_key');
        $apiKey = trim($apiKey);

        if ($apiKey === '') {
            throw new \RuntimeException('Hiányzik a SZAMLAZZHU_AGENT_KEY.');
        }

        // Prefer silent logging for API calls from web requests.
        // Signature differs across package versions, so we keep it defensive.
        if (method_exists(SzamlaAgentAPI::class, 'create')) {
            try {
                $agent = SzamlaAgentAPI::create($apiKey, (bool) config('szamlazzhu.download_pdf'), Log::LOG_LEVEL_OFF);
            } catch (\Throwable $e) {
                $agent = SzamlaAgentAPI::create($apiKey);
            }
        } else {
            $agent = new SzamlaAgentAPI($apiKey);
        }

        // The package tries to save XML/log/pdf into its own directory by default.
        // In Laravel deployments, vendor is often not writable; redirect to storage when possible,
        // otherwise disable XML saving.
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

            if (method_exists($agent, 'setXmlFileSave')) {
                $agent->setXmlFileSave(false);
            }
            if (method_exists($agent, 'setRequestXmlFileSave')) {
                $agent->setRequestXmlFileSave(false);
            }
            if (method_exists($agent, 'setResponseXmlFileSave')) {
                $agent->setResponseXmlFileSave(false);
            }
        } catch (\Throwable $e) {
            // ignore: file logging should not block invoice generation
        }

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

        $sellerName = config('szamlazzhu.seller_name');
        $sellerTax = config('szamlazzhu.seller_tax_number');
        if (is_string($sellerName) && trim($sellerName) !== '' && is_string($sellerTax) && trim($sellerTax) !== '') {
            $invoice->setSeller(new Seller(trim($sellerName), trim($sellerTax)));
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
}
