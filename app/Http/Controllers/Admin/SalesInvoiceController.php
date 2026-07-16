<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        return view('admin.documents.sales-invoices', [
            'companies' => $companies,
            'defaultCompanyId' => $defaultCompanyId,
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
            'invoice_number' => 'required|string|max:255|unique:sales_invoices,invoice_number',
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
            $invoice = SalesInvoice::create($payload);

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
            'invoice_number' => 'required|string|max:255|unique:sales_invoices,invoice_number,' . $invoice->id,
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
