<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySite;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Services\InvoiceServiceInterface;
use App\Services\SzamlazzHu\Dto\CustomerData;
use App\Services\SzamlazzHu\Dto\InvoiceData;
use App\Services\SzamlazzHu\Dto\ItemData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class SalesInvoiceController extends Controller
{
    public function index()
    {
        $companies = Company::query()
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'tax_number',
                'country',
                'zip_code',
                'city',
                'address_line',
                'email',
                'phone',
                'bank_account',
                'is_default',
            ]);

        $defaultCompanyId = optional($companies->firstWhere('is_default', true))->id;

        $companySites = CompanySite::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return view('admin.documents.sales-invoices', [
            'companies' => $companies,
            'defaultCompanyId' => $defaultCompanyId,
            'companySites' => $companySites,
        ]);
    }

    public function data()
    {
        $invoices = SalesInvoice::query()->select([
            'id',
            'company_id',
            'invoice_number',
            'partner_name',
            'issued_at',
            'due_at',
            'currency',
            'gross_total',
            'status',
            'payment_status',
            'pdf_path',
            'created_at as created',
            'updated_at as updated',
        ]);

        return DataTables::of($invoices)
            ->addColumn('action', function ($invoice) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-sales-invoice')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $invoice->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-sales-invoice')) {
            return response()->json(['message' => 'Nincs jogosultságod létrehozni.'], 403);
        }

        $validated = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'invoice_number' => 'nullable|string|max:255|unique:sales_invoices,invoice_number',
            'invoice_type' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'payment_status' => 'nullable|string|max:50',

            'partner_name' => 'required|string|max:255',
            'partner_tax_number' => 'nullable|string|max:255',
            'partner_vat_number' => 'nullable|string|max:255',
            'partner_country' => 'nullable|string|max:2',
            'partner_zip_code' => 'nullable|string|max:255',
            'partner_city' => 'nullable|string|max:255',
            'partner_address_line' => 'nullable|string|max:255',
            'partner_email' => 'nullable|email|max:255',
            'partner_phone' => 'nullable|string|max:255',

            'payment_method' => 'required|string|max:255',
            'payment_reference' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|string|max:255',

            'issued_at' => 'nullable|date',
            'fulfilled_at' => 'nullable|date',
            'due_at' => 'nullable|date',
            'settled_at' => 'nullable|date',

            'currency' => 'nullable|string|size:3',
            'exchange_rate' => 'nullable|numeric',
            'prices_include_vat' => 'nullable|boolean',

            'net_total' => 'nullable|integer',
            'vat_total' => 'nullable|integer',
            'gross_total' => 'nullable|integer',
            'paid_amount' => 'nullable|integer',
            'outstanding_amount' => 'nullable|integer',
            'rounding_amount' => 'nullable|integer',

            'note_before_items' => 'nullable|string',
            'note_after_items' => 'nullable|string',
            'note' => 'nullable|string',

            'items_json' => 'nullable|string',
        ]);

        $companyId = $validated['company_id'] ?? null;
        if (!$companyId) {
            $companyId = Company::query()->where('status', 'active')->where('is_default', true)->value('id');
        }
        $company = $companyId ? Company::query()->where('status', 'active')->find($companyId) : null;
        if (!$company) {
            return response()->json(['message' => 'Kérlek válassz egy céget.'], 422);
        }

        $payload = array_merge([
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'currency' => 'HUF',
            'prices_include_vat' => true,
        ], $validated);

        $payload['company_id'] = $company->id;
        $payload['company_name'] = $company->name;
        $payload['company_tax_number'] = $company->tax_number;
        $payload['company_country'] = $company->country;
        $payload['company_zip_code'] = $company->zip_code;
        $payload['company_city'] = $company->city;
        $payload['company_address_line'] = $company->address_line;
        $payload['company_email'] = $company->email;
        $payload['company_phone'] = $company->phone;
        $payload['company_bank_account'] = $company->bank_account;

        $invoice = DB::transaction(function () use ($payload, $request) {
            $invoiceNumber = trim((string) ($payload['invoice_number'] ?? ''));
            $payload['invoice_number'] = $invoiceNumber !== '' ? $invoiceNumber : 'DRAFT-' . uniqid();

            $invoice = SalesInvoice::create($payload);

            if (str_starts_with((string) $invoice->invoice_number, 'DRAFT-')) {
                $invoice->update([
                    'invoice_number' => 'DRAFT-' . $invoice->id,
                ]);
            }

            $this->syncItemsFromJson($invoice->id, (string) $request->input('items_json', '[]'));

            $this->recalculateTotals($invoice);

            return $invoice;
        });

        return response()->json([
            'message' => 'Sikeres mentés!',
            'invoice' => $invoice,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-sales-invoice')) {
            return response()->json(['message' => 'Nincs jogosultságod szerkeszteni.'], 403);
        }

        $invoice = SalesInvoice::findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'invoice_number' => 'nullable|string|max:255|unique:sales_invoices,invoice_number,' . $invoice->id,
            'invoice_type' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'payment_status' => 'nullable|string|max:50',

            'partner_name' => 'required|string|max:255',
            'partner_tax_number' => 'nullable|string|max:255',
            'partner_vat_number' => 'nullable|string|max:255',
            'partner_country' => 'nullable|string|max:2',
            'partner_zip_code' => 'nullable|string|max:255',
            'partner_city' => 'nullable|string|max:255',
            'partner_address_line' => 'nullable|string|max:255',
            'partner_email' => 'nullable|email|max:255',
            'partner_phone' => 'nullable|string|max:255',

            'payment_method' => 'required|string|max:255',
            'payment_reference' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|string|max:255',

            'issued_at' => 'nullable|date',
            'fulfilled_at' => 'nullable|date',
            'due_at' => 'nullable|date',
            'settled_at' => 'nullable|date',

            'currency' => 'nullable|string|size:3',
            'exchange_rate' => 'nullable|numeric',
            'prices_include_vat' => 'nullable|boolean',

            'net_total' => 'nullable|integer',
            'vat_total' => 'nullable|integer',
            'gross_total' => 'nullable|integer',
            'paid_amount' => 'nullable|integer',
            'outstanding_amount' => 'nullable|integer',
            'rounding_amount' => 'nullable|integer',

            'note_before_items' => 'nullable|string',
            'note_after_items' => 'nullable|string',
            'note' => 'nullable|string',

            'items_json' => 'nullable|string',
        ]);

        $companyId = $validated['company_id'] ?? null;
        if (!$companyId) {
            $companyId = Company::query()->where('status', 'active')->where('is_default', true)->value('id');
        }
        $company = $companyId ? Company::query()->where('status', 'active')->find($companyId) : null;
        if (!$company) {
            return response()->json(['message' => 'Kérlek válassz egy céget.'], 422);
        }

        $validated['company_id'] = $company->id;
        $validated['company_name'] = $company->name;
        $validated['company_tax_number'] = $company->tax_number;
        $validated['company_country'] = $company->country;
        $validated['company_zip_code'] = $company->zip_code;
        $validated['company_city'] = $company->city;
        $validated['company_address_line'] = $company->address_line;
        $validated['company_email'] = $company->email;
        $validated['company_phone'] = $company->phone;
        $validated['company_bank_account'] = $company->bank_account;

        $invoice = DB::transaction(function () use ($invoice, $validated, $request) {
            $invoiceNumber = trim((string) ($validated['invoice_number'] ?? ''));
            if ($invoiceNumber === '') {
                unset($validated['invoice_number']);
            }

            $invoice->update($validated);

            $this->syncItemsFromJson($invoice->id, (string) $request->input('items_json', '[]'));

            $this->recalculateTotals($invoice);

            return $invoice;
        });

        return response()->json([
            'message' => 'Sikeres frissítés!',
            'invoice' => $invoice,
        ], 200);
    }

    public function previewInvoicePdf(Request $request, InvoiceServiceInterface $invoiceService)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-sales-invoice')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $validated = $request->validate([
            'partner_name' => 'required|string|max:255',
            'partner_tax_number' => 'nullable|string|max:255',
            'partner_country' => 'nullable|string|max:2',
            'partner_zip_code' => 'required|string|max:255',
            'partner_city' => 'required|string|max:255',
            'partner_address_line' => 'required|string|max:255',
            'payment_method' => 'required|string|max:255',
            'currency' => 'nullable|string|size:3',
            'items_json' => 'required|string',
        ]);

        $itemsRaw = json_decode((string) $validated['items_json'], true);
        if (!is_array($itemsRaw) || count($itemsRaw) === 0) {
            return response()->json(['message' => 'Nincs tétel a számlában.'], 422);
        }

        $items = [];
        foreach ($itemsRaw as $row) {
            if (!is_array($row)) {
                continue;
            }

            $name = (string) ($row['name'] ?? '');
            $qty = (float) ($row['quantity'] ?? 0);
            $grossUnit = (float) ($row['unit_gross_price'] ?? 0);
            $discount = (float) ($row['discount_percent'] ?? 0);
            $vatPercent = (int) round((float) ($row['vat_percent'] ?? 0));
            $unit = (string) ($row['unit_abbreviation'] ?? 'db');

            if (trim($name) === '' || $qty <= 0) {
                continue;
            }

            $discountedGrossUnit = $grossUnit * (1 - ($discount / 100));
            $div = 1 + (max(0, $vatPercent) / 100);
            $netUnit = $div > 0 ? ($discountedGrossUnit / $div) : $discountedGrossUnit;

            $items[] = new ItemData(
                name: $name,
                quantity: $qty,
                unitPrice: (float) $netUnit,
                vatPercent: max(0, $vatPercent),
                unit: trim($unit) !== '' ? $unit : 'db',
            );
        }

        if (count($items) === 0) {
            return response()->json(['message' => 'A tételek érvénytelenek.'], 422);
        }

        $invoiceData = new InvoiceData(
            customer: new CustomerData(
                name: (string) $validated['partner_name'],
                zip: (string) $validated['partner_zip_code'],
                city: (string) $validated['partner_city'],
                address: (string) $validated['partner_address_line'],
                country: (string) ($validated['partner_country'] ?? 'HU'),
                taxNumber: $validated['partner_tax_number'] ? (string) $validated['partner_tax_number'] : null,
                email: null,
            ),
            items: $items,
            paymentMethod: (string) $validated['payment_method'],
            currency: (string) ($validated['currency'] ?? 'HUF'),
        );

        try {
            $pdfBytes = $invoiceService->createInvoicePdf($invoiceData, true);

            return response($pdfBytes, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="invoice-preview.pdf"',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ], 502);
        }
    }

    public function issueInvoicePdf(Request $request, int $id, InvoiceServiceInterface $invoiceService)
    {
        $user = auth('admin')->user();
        if (!$user || (!$user->can('create-sales-invoice') && !$user->can('edit-sales-invoice'))) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $invoice = SalesInvoice::query()->findOrFail($id);

        $validated = $request->validate([
            'partner_name' => 'required|string|max:255',
            'partner_tax_number' => 'nullable|string|max:255',
            'partner_country' => 'nullable|string|max:2',
            'partner_zip_code' => 'required|string|max:255',
            'partner_city' => 'required|string|max:255',
            'partner_address_line' => 'required|string|max:255',
            'payment_method' => 'required|string|max:255',
            'currency' => 'nullable|string|size:3',
            'items_json' => 'required|string',
        ]);

        $itemsRaw = json_decode((string) $validated['items_json'], true);
        if (!is_array($itemsRaw) || count($itemsRaw) === 0) {
            return response()->json(['message' => 'Nincs tétel a számlában.'], 422);
        }

        $items = [];
        foreach ($itemsRaw as $row) {
            if (!is_array($row)) {
                continue;
            }

            $name = (string) ($row['name'] ?? '');
            $qty = (float) ($row['quantity'] ?? 0);
            $grossUnit = (float) ($row['unit_gross_price'] ?? 0);
            $discount = (float) ($row['discount_percent'] ?? 0);
            $vatPercent = (int) round((float) ($row['vat_percent'] ?? 0));
            $unit = (string) ($row['unit_abbreviation'] ?? 'db');

            if (trim($name) === '' || $qty <= 0) {
                continue;
            }

            $discountedGrossUnit = $grossUnit * (1 - ($discount / 100));
            $div = 1 + (max(0, $vatPercent) / 100);
            $netUnit = $div > 0 ? ($discountedGrossUnit / $div) : $discountedGrossUnit;

            $items[] = new ItemData(
                name: $name,
                quantity: $qty,
                unitPrice: (float) $netUnit,
                vatPercent: max(0, $vatPercent),
                unit: trim($unit) !== '' ? $unit : 'db',
            );
        }

        if (count($items) === 0) {
            return response()->json(['message' => 'A tételek érvénytelenek.'], 422);
        }

        $invoiceData = new InvoiceData(
            customer: new CustomerData(
                name: (string) $validated['partner_name'],
                zip: (string) $validated['partner_zip_code'],
                city: (string) $validated['partner_city'],
                address: (string) $validated['partner_address_line'],
                country: (string) ($validated['partner_country'] ?? 'HU'),
                taxNumber: $validated['partner_tax_number'] ? (string) $validated['partner_tax_number'] : null,
                email: null,
            ),
            items: $items,
            paymentMethod: (string) $validated['payment_method'],
            currency: (string) ($validated['currency'] ?? 'HUF'),
        );

        try {
            $pdfBytes = $invoiceService->createInvoicePdf($invoiceData, false);

            $month = now()->format('Y-m');
            $dir = 'private/szamlazzhu/kimeno/' . $month;
            $fileName = 'kimeno-szamla-' . $invoice->id . '.pdf';
            $relativePath = $dir . '/' . $fileName;

            Storage::disk('local')->put($relativePath, $pdfBytes);

            $invoice->update([
                'pdf_path' => $relativePath,
                'status' => 'issued',
            ]);

            return response($pdfBytes, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ], 502);
        }
    }

    private function syncItemsFromJson(int $salesInvoiceId, string $itemsJson): void
    {
        $decoded = json_decode($itemsJson, true);
        if (!is_array($decoded)) {
            return;
        }

        $decoded = array_values(array_filter($decoded, fn ($row) => is_array($row)));

        SalesInvoiceItem::query()->where('sales_invoice_id', $salesInvoiceId)->delete();

        $sort = 0;
        foreach ($decoded as $row) {
            $productId = $row['product_id'] ?? null;
            if ($productId !== null && $productId !== '' && !is_numeric($productId)) {
                $productId = null;
            }

            $quantity = $row['quantity'] ?? 1;
            if (!is_numeric($quantity)) {
                $quantity = 1;
            }

            $unitGross = $row['unit_gross_price'] ?? 0;
            if (!is_numeric($unitGross)) {
                $unitGross = 0;
            }

            $grossTotal = $row['gross_total'] ?? null;
            if ($grossTotal !== null && !is_numeric($grossTotal)) {
                $grossTotal = null;
            }

            SalesInvoiceItem::create([
                'sales_invoice_id' => $salesInvoiceId,
                'product_id' => $productId !== null ? (int) $productId : null,
                'sort_order' => $sort,
                'name' => (string) ($row['name'] ?? ''),
                'quantity' => (float) $quantity,
                'unit_gross_price' => (int) round((float) $unitGross),
                'gross_total' => $grossTotal !== null ? (int) round((float) $grossTotal) : null,
            ]);

            $sort++;
        }
    }

    private function recalculateTotals(SalesInvoice $invoice): void
    {
        $items = SalesInvoiceItem::query()->where('sales_invoice_id', $invoice->id)->get();

        $gross = 0;
        foreach ($items as $item) {
            $rowTotal = $item->gross_total;
            if ($rowTotal === null) {
                $rowTotal = (int) round(((float) $item->quantity) * ((float) ($item->unit_gross_price ?? 0)));
            }
            $gross += (int) $rowTotal;
        }

        $invoice->update([
            'gross_total' => $gross,
        ]);
    }
}
