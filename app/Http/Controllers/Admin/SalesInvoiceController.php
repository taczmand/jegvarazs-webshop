<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyInvoicePrefix;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesInvoicePayment;
use App\Models\Warehouse;
use App\Services\InvoiceServiceInterface;
use App\Services\SzamlazzHu\SzamlazzHuInvoiceService;
use App\Services\SzamlazzHu\Dto\CustomerData;
use App\Services\SzamlazzHu\Dto\InvoiceData;
use App\Services\SzamlazzHu\Dto\ItemData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
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

        $companyInvoicePrefixes = CompanyInvoicePrefix::query()
            ->where('is_active', true)
            ->orderBy('company_id')
            ->orderByDesc('is_default')
            ->orderBy('prefix')
            ->get([
                'id',
                'company_id',
                'prefix',
                'is_default',
                'is_active',
            ]);

        $warehouses = Warehouse::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return view('admin.documents.sales-invoices', [
            'companies' => $companies,
            'defaultCompanyId' => $defaultCompanyId,
            'companyInvoicePrefixes' => $companyInvoicePrefixes,
            'warehouses' => $warehouses,
        ]);
    }

    public function data()
    {
        $invoices = SalesInvoice::query()->select([
            'id',
            'company_id',
            'invoice_number',
            'invoice_type',
            'partner_name',
            'payment_method',
            'issued_at',
            'due_at',
            'gross_total',
            'outstanding_amount',
            'status',
            'payment_status',
            'note',
            'pdf_path',
            'created_at as created',
            'updated_at as updated',
        ]);

        return DataTables::of($invoices)
            ->addColumn('payment_method', function ($invoice) {
                $translations = [
                    'bank_transfer'   => 'átutalás',
                    'cash'   => 'készpénz',
                    'credtit_card' => 'bankkártya'
                ];
                return $translations[$invoice->payment_method] ?? ucfirst($invoice->payment_method);
            })
            ->addColumn('status', function ($invoice) {
                $translations = [
                    'draft'   => 'Piszkozat',
                    'issued'   => 'Kiállítva',
                    'cancelled' => 'Érvénytelenítve'
                ];
                return $translations[$invoice->status] ?? ucfirst($invoice->status);
            })
            ->addColumn('payment_status', function ($invoice) {
                $translations = [
                    'unpaid'   => 'Nincs kifizetve',
                    'paid'   => 'Kifizetve',
                    'partially_paid'   => 'Részben van kifizetve',
                    'overdue' => 'Lejárt'
                ];
                return $translations[$invoice->payment_status] ?? ucfirst($invoice->payment_status);
            })
            ->addColumn('invoice_type', function ($invoice) {
                $translations = [
                    'storno'   => 'Sztornó',
                    'normal'   => 'Papír',
                    'electronic' => 'E-számla',
                    'correction' => 'Helyesbítő',
                ];
                return $translations[$invoice->invoice_type] ?? ucfirst($invoice->invoice_type);
            })
            ->editColumn('gross_total', function ($invoice) {
                return number_format($invoice->gross_total, 0, ',', ' ') . ' Ft';
            })
            ->editColumn('outstanding_amount', function ($invoice) {
                return number_format($invoice->outstanding_amount, 0, ',', ' ') . ' Ft';
            })
            ->addColumn('action', function ($invoice) {
                $user = auth('admin')->user();
                $buttons = '';

                if (!empty($invoice->pdf_path) && $user && $user->can('view-sales-invoices')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-outline-secondary pdf" data-id="' . $invoice->id . '" title="PDF megnyitása">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                    ';

                    if ($invoice->status === 'issued' && $user->can('edit-sales-invoice')) {
                        $buttons .= '
                            <button class="btn btn-sm btn-outline-danger storno" data-id="' . $invoice->id . '" title="Érvénytelenítő számla készítése">
                                <i class="fas fa-ban"></i>
                            </button>
                        ';
                        if ($invoice->invoice_type === 'normal') {
                            $buttons .= '
                                <button class="btn btn-sm btn-outline-primary correction" data-id="' . $invoice->id . '" title="Helyesbítő számla készítése">
                                    <i class="fas fa-file-invoice"></i>
                                </button>
                            ';
                        }
                        $buttons .= '
                            <button class="btn btn-sm btn-outline-secondary finances" data-id="' . $invoice->id . '" title="Pénzügyek">
                                <i class="fas fa-credit-card"></i>
                            </button>
                        ';
                    }
                }

                if ($invoice->status === 'draft' && $user && $user->can('edit-sales-invoice')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $invoice->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                /*if ($invoice->status === 'issued' && $user && $user->can('edit-sales-invoice')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-outline-primary view" data-id="' . $invoice->id . '" title="Adatok megtekintése">
                            <i class="fas fa-eye"></i>
                        </button>
                    ';
                }*/

                return $buttons;
            })
            ->editColumn('issued_at', function ($invoice) {
                return $invoice->issued_at ? $invoice->issued_at->format('Y-m-d') : '';
            })
            ->editColumn('due_at', function ($invoice) {
                return $invoice->due_at ? $invoice->due_at->format('Y-m-d') : '';
            })
            ->editColumn('created_at', function ($invoice) {
                return $invoice->created_at ? $invoice->created_at->format('Y-m-d H:i:s') : '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function createCorrection(int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-sales-invoice')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $original = SalesInvoice::query()->with(['items'])->findOrFail($id);
        if ((string) $original->status !== 'issued') {
            return response()->json(['message' => 'Csak kiállított számlából készíthető helyesbítő számla.'], 422);
        }

        $correction = DB::transaction(function () use ($original) {
            $payload = $original->getAttributes();

            unset(
                $payload['id'],
                $payload['created_at'],
                $payload['updated_at'],
                $payload['items'],
                $payload['pdf_path'],
                $payload['external_id'],
                $payload['invoice_number'],
                $payload['stock_deducted_at']
            );

            $payload['status'] = 'draft';
            $payload['payment_status'] = 'unpaid';
            $payload['invoice_type'] = 'correction';
            $payload['issued_at'] = null;
            $payload['fulfilled_at'] = null;
            $payload['due_at'] = null;
            $payload['settled_at'] = null;
            $payload['correction_of_sales_invoice_id'] = $original->id;
            $payload['storno_of_sales_invoice_id'] = null;
            $payload['note'] = 'Helyesbítő számla ehhez: ' . (string) ($original->invoice_number ?? '');

            $correction = SalesInvoice::create(array_merge([
                'invoice_number' => 'DRAFT-' . uniqid(),
            ], $payload));

            if (str_starts_with((string) $correction->invoice_number, 'DRAFT-')) {
                $correction->update([
                    'invoice_number' => 'DRAFT-' . $correction->id,
                ]);
            }

            foreach ($original->items as $it) {
                $qty = (float) ($it->quantity ?? 0);
                $negQty = $qty === 0.0 ? 0.0 : (-1 * abs($qty));

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $correction->id,
                    'product_id' => $it->product_id,
                    'warehouse_id' => $it->warehouse_id,
                    'sort_order' => (int) ($it->sort_order ?? 0),
                    'name' => (string) ($it->name ?? ''),
                    'sku' => $it->sku,
                    'unit' => $it->unit,
                    'quantity' => $negQty,
                    'discount_percent' => $it->discount_percent,
                    'discount_amount' => $it->discount_amount,
                    'vat_percent' => $it->vat_percent,
                    'vat_code' => $it->vat_code,
                    'unit_net_price' => $it->unit_net_price,
                    'unit_gross_price' => $it->unit_gross_price,
                    'net_total' => $it->net_total !== null ? (int) (-1 * abs((int) $it->net_total)) : null,
                    'vat_total' => $it->vat_total !== null ? (int) (-1 * abs((int) $it->vat_total)) : null,
                    'gross_total' => $it->gross_total !== null ? (int) (-1 * abs((int) $it->gross_total)) : null,
                    'note' => $it->note,
                ]);
            }

            $this->recalculateTotals($correction);

            return $correction;
        });

        return response()->json([
            'message' => 'Helyesbítő számla létrehozva.',
            'invoice' => $correction,
        ], 200);
    }

    public function storno(int $id, InvoiceServiceInterface $invoiceService)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-sales-invoice')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $invoice = SalesInvoice::query()->with(['items'])->findOrFail($id);
        if ((string) $invoice->status !== 'issued') {
            return response()->json(['message' => 'Csak kiállított számla sztornózható.'], 422);
        }

        if ((string) $invoice->status === 'cancelled') {
            return response()->json(['message' => 'A számla már sztornózva van.'], 422);
        }

        $company = null;
        if (!empty($invoice->company_id)) {
            $company = Company::query()->where('status', 'active')->find((int) $invoice->company_id);
        }
        if (!$company) {
            return response()->json(['message' => 'A számlához nincs érvényes cég rendelve.'], 422);
        }

        if (!is_string($company->billing_provider_api_key ?? null) || trim((string) $company->billing_provider_api_key) === '') {
            return response()->json(['message' => 'A számlához tartozó céghez nincs beállítva API kulcs.'], 422);
        }

        $originalNumber = trim((string) $invoice->invoice_number);
        if ($originalNumber === '' || str_starts_with($originalNumber, 'DRAFT-')) {
            return response()->json(['message' => 'Hiányzik az eredeti számlaszám, sztornó nem indítható.'], 422);
        }

        try {
            $result = $invoiceService->createReverseInvoicePdfWithNumber(
                $originalNumber,
                (string) $company->billing_provider_api_key,
            );

            $pdfBytes = (string) ($result['pdf'] ?? '');
            $stornoNumber = isset($result['invoice_number']) ? trim((string) $result['invoice_number']) : '';
            if ($pdfBytes === '') {
                throw new \RuntimeException('Számlázz.hu sztornó PDF generálása sikertelen.');
            }

            $month = now()->format('Y-m');
            $dir = 'szamlazzhu/kimeno-storno/' . $month;
            $fileName = 'kimeno-szamla-storno-' . $invoice->id . '.pdf';
            $relativePath = $dir . '/' . $fileName;

            $absoluteDir = Storage::disk('local')->path($dir);
            if (!is_dir($absoluteDir)) {
                File::makeDirectory($absoluteDir, 0775, true);
            }
            Storage::disk('local')->makeDirectory($dir);

            Storage::disk('local')->put($relativePath, $pdfBytes);

            DB::transaction(function () use ($invoice, $relativePath, $stornoNumber) {
                $invoice = SalesInvoice::query()->with(['items'])->lockForUpdate()->findOrFail($invoice->id);

                if ((string) $invoice->status === 'cancelled') {
                    return;
                }

                $this->restoreStockForCancelledSalesInvoice($invoice);

                SalesInvoice::create([
                    'company_id' => $invoice->company_id,
                    'invoice_number' => $stornoNumber,
                    'invoice_type' => 'storno',
                    'status' => 'issued',
                    'payment_status' => $invoice->payment_status,
                    'partner_name' => $invoice->partner_name,
                    'partner_tax_number' => $invoice->partner_tax_number,
                    'partner_vat_number' => $invoice->partner_vat_number,
                    'partner_country' => $invoice->partner_country,
                    'partner_zip_code' => $invoice->partner_zip_code,
                    'partner_city' => $invoice->partner_city,
                    'partner_address_line' => $invoice->partner_address_line,
                    'partner_email' => $invoice->partner_email,
                    'partner_phone' => $invoice->partner_phone,
                    'company_name' => $invoice->company_name,
                    'company_tax_number' => $invoice->company_tax_number,
                    'company_country' => $invoice->company_country,
                    'company_zip_code' => $invoice->company_zip_code,
                    'company_city' => $invoice->company_city,
                    'company_address_line' => $invoice->company_address_line,
                    'company_email' => $invoice->company_email,
                    'company_phone' => $invoice->company_phone,
                    'company_bank_account' => $invoice->company_bank_account,
                    'payment_method' => $invoice->payment_method,
                    'payment_reference' => $invoice->payment_reference,
                    'issued_at' => $invoice->issued_at,
                    'fulfilled_at' => $invoice->fulfilled_at,
                    'due_at' => $invoice->due_at,
                    'settled_at' => $invoice->settled_at,
                    'currency' => $invoice->currency,
                    'exchange_rate' => $invoice->exchange_rate,
                    'prices_include_vat' => $invoice->prices_include_vat,
                    'net_total' => $invoice->net_total,
                    'vat_total' => $invoice->vat_total,
                    'gross_total' => $invoice->gross_total,
                    'paid_amount' => $invoice->paid_amount,
                    'outstanding_amount' => $invoice->outstanding_amount,
                    'rounding_amount' => $invoice->rounding_amount,
                    'related_order_id' => $invoice->related_order_id,
                    'related_contract_id' => $invoice->related_contract_id,
                    'storno_of_sales_invoice_id' => $invoice->id,
                    'external_provider' => $invoice->external_provider,
                    'external_id' => $invoice->external_id,
                    'pdf_path' => $relativePath,
                    'note' => 'Érvénytelenített számla: '.$invoice->invoice_number
                ]);

                $invoice->update([
                    'status' => 'cancelled'
                ]);
            });

            return response()->json([
                'message' => 'Sztornó sikeres.',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ], 502);
        }
    }

    public function pdf(int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-sales-invoices')) {
            abort(403);
        }

        $invoice = SalesInvoice::query()->findOrFail($id);
        $path = (string) ($invoice->pdf_path ?? '');
        if ($path === '') {
            abort(404);
        }

        $disk = Storage::disk('local');
        $candidatePaths = [$path];
        if (str_starts_with($path, 'private/')) {
            $candidatePaths[] = ltrim(substr($path, strlen('private/')), '/');
        }

        $resolved = null;
        foreach ($candidatePaths as $candidate) {
            if ($candidate === '') {
                continue;
            }

            if ($disk->exists($candidate)) {
                $resolved = $disk->path($candidate);
                break;
            }
        }

        if (!$resolved || !file_exists($resolved)) {
            abort(404);
        }

        return response()->file($resolved, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="kimeno-szamla-' . $invoice->id . '.pdf"',
        ]);
    }

    public function show(int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-sales-invoice')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $invoice = SalesInvoice::query()->with(['items'])->findOrFail($id);

        return response()->json([
            'invoice' => $invoice,
            'items' => $invoice->items,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-sales-invoice')) {
            return response()->json(['message' => 'Nincs jogosultságod létrehozni.'], 403);
        }

        $validated = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'company_invoice_prefix_id' => 'nullable|integer|exists:company_invoice_prefixes,id',
            'invoice_number' => 'nullable|string|max:255',
            'invoice_type' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'payment_status' => 'nullable|string|max:50',
            'correction_of_sales_invoice_id' => 'nullable|integer|exists:sales_invoices,id',

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

            'note_for_document' => 'nullable|string',
            'note' => 'nullable|string',

            'items_json' => 'nullable|string',
        ]);

        // items_json is not a DB column; it's only used to sync invoice items.
        unset($validated['items_json']);

        $companyId = $validated['company_id'] ?? null;
        if (!$companyId) {
            $companyId = Company::query()->where('status', 'active')->where('is_default', true)->value('id');
        }
        $company = $companyId ? Company::query()->where('status', 'active')->find($companyId) : null;
        if (!$company) {
            return response()->json(['message' => 'Kérlek válassz egy céget.'], 422);
        }

        $prefixId = $validated['company_invoice_prefix_id'] ?? null;
        $prefix = null;
        if ($prefixId !== null) {
            $prefixModel = CompanyInvoicePrefix::query()
                ->whereKey((int) $prefixId)
                ->where('company_id', (int) $company->id)
                ->where('is_active', true)
                ->first();

            if (!$prefixModel) {
                return response()->json(['message' => 'A kiválasztott számla előtag nem érvényes ehhez a céghez.'], 422);
            }

            $prefix = (string) $prefixModel->prefix;
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

        $payload['company_invoice_prefix_id'] = $prefixId;
        $payload['company_invoice_prefix'] = $prefix;

        $invoice = DB::transaction(function () use ($payload, $request) {
            $invoiceNumber = trim((string) ($payload['invoice_number'] ?? ''));

            // Ha nincs számlaszám, generálunk egy új draft számot.
            if ($invoiceNumber === '') {
                $payload['invoice_number'] = 'DRAFT-' . uniqid();

                $invoice = SalesInvoice::create($payload);

                $invoice->update([
                    'invoice_number' => 'DRAFT-' . $invoice->id,
                ]);
            } elseif (str_starts_with($invoiceNumber, 'DRAFT-')) {
                // Meglévő draft számla keresése.
                $invoice = SalesInvoice::query()
                    ->where('invoice_number', $invoiceNumber)
                    ->first();

                if (!$invoice) {
                    throw ValidationException::withMessages([
                        'invoice_number' => 'A megadott piszkozat számla nem található.',
                    ]);
                }

                // Meglévő draft frissítése.
                $invoice->update($payload);
            } else {
                $invoice = SalesInvoice::query()
                    ->where('invoice_number', $invoiceNumber)
                    ->first();

                if (!$invoice) {
                    throw ValidationException::withMessages([
                        'invoice_number' => 'Ez a számlaszám már létezik.',
                    ]);
                } else {
                    $invoice->update($payload);
                }

            }

            $this->syncItemsFromJson(
                $invoice->id,
                (string) $request->input('items_json', '[]')
            );

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
            'company_invoice_prefix_id' => 'nullable|integer|exists:company_invoice_prefixes,id',
            'invoice_number' => 'nullable|string|max:255|unique:sales_invoices,invoice_number,' . $invoice->id,
            'invoice_type' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'payment_status' => 'nullable|string|max:50',
            'correction_of_sales_invoice_id' => 'nullable|integer|exists:sales_invoices,id',

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

            'note_for_document' => 'nullable|string',
            'note' => 'nullable|string',

            'items_json' => 'nullable|string',
        ]);

        // items_json is not a DB column; it's only used to sync invoice items.
        unset($validated['items_json']);

        $companyId = $validated['company_id'] ?? null;
        if (!$companyId) {
            $companyId = Company::query()->where('status', 'active')->where('is_default', true)->value('id');
        }
        $company = $companyId ? Company::query()->where('status', 'active')->find($companyId) : null;
        if (!$company) {
            return response()->json(['message' => 'Kérlek válassz egy céget.'], 422);
        }

        $prefixId = $validated['company_invoice_prefix_id'] ?? null;
        $prefix = null;
        if ($prefixId !== null) {
            $prefixModel = CompanyInvoicePrefix::query()
                ->whereKey((int) $prefixId)
                ->where('company_id', (int) $company->id)
                ->where('is_active', true)
                ->first();

            if (!$prefixModel) {
                return response()->json(['message' => 'A kiválasztott számla előtag nem érvényes ehhez a céghez.'], 422);
            }

            $prefix = (string) $prefixModel->prefix;
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

        $validated['company_invoice_prefix_id'] = $prefixId;
        $validated['company_invoice_prefix'] = $prefix;

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

        $validator = Validator::make($request->all(), [
            'company_id' => 'required|integer|exists:companies,id',
            'company_invoice_prefix_id' => 'nullable|integer|exists:company_invoice_prefixes,id',
            'partner_name' => 'required|string|max:255',
            'partner_tax_number' => 'nullable|string|max:255',
            'partner_country' => 'nullable|string|max:2',
            'partner_zip_code' => 'required|string|max:255',
            'partner_city' => 'required|string|max:255',
            'partner_address_line' => 'required|string|max:255',
            'payment_method' => 'required|string|max:255',
            'currency' => 'nullable|string|size:3',
            'note_for_document' => 'nullable|string',
            'items_json' => 'required|string',
        ], [
            'company_id.required' => 'A számlázó cég kiválasztása kötelező.',
            'company_id.integer' => 'A számlázó cég azonosítója hibás.',
            'company_id.exists' => 'A kiválasztott számlázó cég nem létezik.',

            'partner_name.required' => 'A partner neve kötelező.',
            'partner_zip_code.required' => 'Az irányítószám megadása kötelező.',
            'partner_city.required' => 'A város megadása kötelező.',
            'partner_address_line.required' => 'A cím megadása kötelező.',

            'payment_method.required' => 'A fizetési mód kiválasztása kötelező.',

            'currency.size' => 'A pénznemnek 3 karakterből kell állnia.',

            'items_json.required' => 'Legalább egy számlatétel megadása kötelező.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Hiányos vagy hibás adatok.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $company = Company::query()->where('status', 'active')->find((int) $validated['company_id']);
        if (!$company) {
            return response()->json(['message' => 'Kérlek válassz egy céget.'], 422);
        }

        if (!is_string($company->billing_provider_api_key ?? null) || trim((string) $company->billing_provider_api_key) === '') {
            return response()->json(['message' => 'A kiválasztott céghez nincs beállítva Számlázz.hu API kulcs.'], 422);
        }

        $prefixId = $validated['company_invoice_prefix_id'] ?? null;
        $prefix = null;
        if ($prefixId !== null) {
            $prefixModel = CompanyInvoicePrefix::query()
                ->whereKey((int) $prefixId)
                ->where('company_id', (int) $company->id)
                ->where('is_active', true)
                ->first();
            if (!$prefixModel) {
                return response()->json(['message' => 'A kiválasztott számla előtag nem érvényes ehhez a céghez.'], 422);
            }
            $prefix = (string) $prefixModel->prefix;
        }

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

            if (trim($name) === '' || $qty == 0) {
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
            noteForDocument: isset($validated['note_for_document']) ? (string) $validated['note_for_document'] : null,
            invoicePrefix: $prefix,
            currency: (string) ($validated['currency'] ?? 'HUF'),
            agentKey: $company->billing_provider_api_key ? (string) $company->billing_provider_api_key : null,
        );

        try {
            $invoiceId = $request->input('invoice_id', $request->input('id'));
            $invoice = null;
            if (is_numeric($invoiceId)) {
                $invoice = SalesInvoice::query()->find((int) $invoiceId);
            }

            $isCorrection = false;
            $correctionOfId = null;

            if ($invoice && (string) $invoice->invoice_type === 'correction') {
                $isCorrection = true;
                $correctionOfId = $invoice->correction_of_sales_invoice_id;
            } else {
                $isCorrection = trim((string) $request->input('invoice_type')) === 'correction';
                $correctionOfId = $request->input('correction_of_sales_invoice_id');
            }

            if ($isCorrection) {
                $originalNumber = null;
                if (is_numeric($correctionOfId)) {
                    $originalNumber = SalesInvoice::query()->whereKey((int) $correctionOfId)->value('invoice_number');
                    $originalNumber = is_string($originalNumber) ? trim($originalNumber) : null;
                }

                if (!$originalNumber || $originalNumber === '' || str_starts_with($originalNumber, 'DRAFT-')) {
                    return response()->json(['message' => 'Hiányzik az eredeti számlaszám, helyesbítő előnézet nem indítható.'], 422);
                }

                if ($invoiceService instanceof SzamlazzHuInvoiceService && method_exists($invoiceService, 'createCorrectiveInvoicePdfWithNumber')) {
                    $result = $invoiceService->createCorrectiveInvoicePdfWithNumber($invoiceData, $originalNumber, true);
                    $pdfBytes = (string) ($result['pdf'] ?? '');
                } else {
                    return response()->json(['message' => 'A helyesbítő számla generálása nem támogatott a beállított számlázó szolgáltatóval.'], 422);
                }
            } else {
                $pdfBytes = $invoiceService->createInvoicePdf($invoiceData, true);
            }

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

        $company = null;
        if (!empty($invoice->company_id)) {
            $company = Company::query()->where('status', 'active')->find((int) $invoice->company_id);
        }
        if (!$company) {
            return response()->json(['message' => 'A számlához nincs érvényes cég rendelve.'], 422);
        }

        if (!is_string($company->billing_provider_api_key ?? null) || trim((string) $company->billing_provider_api_key) === '') {
            return response()->json(['message' => 'A számlához tartozó céghez nincs beállítva Számlázz.hu API kulcs.'], 422);
        }

        $validated = $request->validate([
            'partner_name' => 'required|string|max:255',
            'partner_email' => 'nullable|string|email',
            'partner_tax_number' => 'nullable|string|max:255',
            'partner_country' => 'nullable|string|max:2',
            'partner_zip_code' => 'required|string|max:255',
            'partner_city' => 'required|string|max:255',
            'partner_address_line' => 'required|string|max:255',
            'payment_method' => 'required|string|max:255',
            'currency' => 'nullable|string|size:3',
            'note_for_document' => 'nullable|string',
            'items_json' => 'required|string',
            'send_email' => 'nullable|integer'
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

            if (trim($name) === '' || $qty == 0) {
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

        $invoicePrefix = null;
        if (!empty($invoice->company_invoice_prefix)) {
            $invoicePrefix = trim((string) $invoice->company_invoice_prefix);
        } elseif (!empty($invoice->company_invoice_prefix_id)) {
            $invoicePrefix = CompanyInvoicePrefix::query()
                ->whereKey((int) $invoice->company_invoice_prefix_id)
                ->where('company_id', (int) $company->id)
                ->value('prefix');
            $invoicePrefix = is_string($invoicePrefix) ? trim($invoicePrefix) : null;
        }
        if ($invoicePrefix === '') {
            $invoicePrefix = null;
        }

        $invoiceData = new InvoiceData(
            customer: new CustomerData(
                name: (string) $validated['partner_name'],
                zip: (string) $validated['partner_zip_code'],
                city: (string) $validated['partner_city'],
                address: (string) $validated['partner_address_line'],
                country: (string) ($validated['partner_country'] ?? 'HU'),
                taxNumber: $validated['partner_tax_number'] ? (string) $validated['partner_tax_number'] : null,
                email: (string) ($validated['partner_email'] ?? null),
                sendEmail: (int) ($validated['send_email'] ?? 0),
            ),
            items: $items,
            paymentMethod: (string) $validated['payment_method'],
            noteForDocument: isset($validated['note_for_document']) ? (string) $validated['note_for_document'] : null,
            invoicePrefix: $invoicePrefix,
            currency: (string) ($validated['currency'] ?? 'HUF'),
            agentKey: $company->billing_provider_api_key ? (string) $company->billing_provider_api_key : null,
        );

        try {
            $providerInvoiceNumber = null;
            $pdfBytes = null;
            $isCorrection = (string) ($invoice->invoice_type ?? '') === 'correction';
            $originalInvoiceNumber = null;
            if ($isCorrection && !empty($invoice->correction_of_sales_invoice_id)) {
                $originalInvoiceNumber = SalesInvoice::query()->whereKey((int) $invoice->correction_of_sales_invoice_id)->value('invoice_number');
                $originalInvoiceNumber = is_string($originalInvoiceNumber) ? trim($originalInvoiceNumber) : null;
                if ($originalInvoiceNumber === '' || (is_string($originalInvoiceNumber) && str_starts_with($originalInvoiceNumber, 'DRAFT-'))) {
                    $originalInvoiceNumber = null;
                }
            }

            if ($invoiceService instanceof SzamlazzHuInvoiceService) {
                if ($isCorrection) {
                    if (!$originalInvoiceNumber) {
                        return response()->json(['message' => 'Hiányzik az eredeti számlaszám, helyesbítő számla nem állítható ki.'], 422);
                    }
                    if (!method_exists($invoiceService, 'createCorrectiveInvoicePdfWithNumber')) {
                        return response()->json(['message' => 'A helyesbítő számla generálása nem támogatott a használt Számlázz.hu SDK verzióval.'], 422);
                    }

                    $result = $invoiceService->createCorrectiveInvoicePdfWithNumber($invoiceData, $originalInvoiceNumber, false);
                    $pdfBytes = (string) ($result['pdf'] ?? '');
                    $providerInvoiceNumber = isset($result['invoice_number']) ? (string) $result['invoice_number'] : null;
                } else {
                    if (method_exists($invoiceService, 'createInvoicePdfWithNumber')) {
                        $result = $invoiceService->createInvoicePdfWithNumber($invoiceData, false);
                        $pdfBytes = (string) ($result['pdf'] ?? '');
                        $providerInvoiceNumber = isset($result['invoice_number']) ? (string) $result['invoice_number'] : null;
                    } else {
                        $pdfBytes = $invoiceService->createInvoicePdf($invoiceData, false);
                    }
                }
            } else {
                $pdfBytes = $invoiceService->createInvoicePdf($invoiceData, false);
            }

            $providerInvoiceNumber = $providerInvoiceNumber !== null ? trim($providerInvoiceNumber) : null;
            if ($providerInvoiceNumber === '') {
                $providerInvoiceNumber = null;
            }

            if (!is_string($pdfBytes) || $pdfBytes === '') {
                throw new \RuntimeException('Számlázz.hu PDF generálása sikertelen.');
            }

            $month = now()->format('Y-m');
            $dir = ($isCorrection ? 'szamlazzhu/kimeno-helyesbito/' : 'szamlazzhu/kimeno/') . $month;
            $fileName = ($isCorrection ? 'kimeno-szamla-helyesbito-' : 'kimeno-szamla-') . $invoice->id . '.pdf';
            $relativePath = $dir . '/' . $fileName;

            $absoluteDir = Storage::disk('local')->path($dir);
            if (!is_dir($absoluteDir)) {
                File::makeDirectory($absoluteDir, 0775, true);
            }

            Storage::disk('local')->makeDirectory($dir);

            $oldPath = (string) ($invoice->pdf_path ?? '');
            if ($oldPath !== '' && $oldPath !== $relativePath && Storage::disk('local')->exists($oldPath)) {
                Storage::disk('local')->delete($oldPath);
            }

            $written = Storage::disk('local')->put($relativePath, $pdfBytes);
            if ($written !== true) {
                throw new \RuntimeException('A PDF mentése sikertelen (Storage::put false).');
            }

            $resolvedPath = Storage::disk('local')->path($relativePath);
            $exists = Storage::disk('local')->exists($relativePath);
            $fsExists = is_string($resolvedPath) && $resolvedPath !== '' ? file_exists($resolvedPath) : false;
            $size = $fsExists ? (int) @filesize($resolvedPath) : 0;

            logger()->info('SalesInvoice PDF save result', [
                'invoice_id' => $invoice->id,
                'relative_path' => $relativePath,
                'resolved_path' => $resolvedPath,
                'storage_exists' => $exists,
                'fs_exists' => $fsExists,
                'filesize' => $size,
                'bytes_len' => strlen($pdfBytes),
            ]);

            if (!$exists || !$fsExists) {
                throw new \RuntimeException('A PDF mentése sikertelen (a fájl nem található mentés után). Útvonal: ' . $resolvedPath);
            }

            if ($size <= 0) {
                throw new \RuntimeException('A PDF mentése sikertelen (0 bájtos fájl). Útvonal: ' . $resolvedPath);
            }

            if ($invoice->stock_deducted_at === null) {
                DB::transaction(function () use ($invoice) {
                    $invoice = SalesInvoice::query()->lockForUpdate()->findOrFail($invoice->id);
                    if ($invoice->stock_deducted_at === null) {
                        $this->deductStockForIssuedSalesInvoice($invoice);
                    }
                });
            }

            $invoice->update([
                'pdf_path' => $relativePath,
                'invoice_number' => $providerInvoiceNumber ?: $invoice->invoice_number,
                'status' => 'issued',
                'stock_deducted_at' => $invoice->stock_deducted_at ?: now(),
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

            $warehouseId = $row['warehouse_id'] ?? null;
            if ($warehouseId !== null && $warehouseId !== '' && !is_numeric($warehouseId)) {
                $warehouseId = null;
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
                'warehouse_id' => $warehouseId !== null ? (int) $warehouseId : null,
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

    private function deductStockForIssuedSalesInvoice(SalesInvoice $invoice): void
    {
        $items = SalesInvoiceItem::query()->where('sales_invoice_id', $invoice->id)->get();
        if ($items->isEmpty()) {
            return;
        }

        foreach ($items as $item) {
            if (!$item->product_id) {
                continue;
            }

            $warehouseId = (int) ($item->warehouse_id ?? 0);
            if ($warehouseId <= 0) {
                throw new \RuntimeException('Készletcsökkentéshez hiányzik a raktár a számla tételben.');
            }

            $currentQty = (float) (DB::table('product_stocks')
                ->where('warehouse_id', '=', $warehouseId)
                ->where('product_id', '=', (int) $item->product_id)
                ->lockForUpdate()
                ->value('quantity') ?? 0);
            $deduct = (float) ($item->quantity ?? 0);
            $newQty = $currentQty - $deduct;

            DB::table('product_stocks')->updateOrInsert(
                [
                    'warehouse_id' => $warehouseId,
                    'product_id' => (int) $item->product_id,
                ],
                [
                    'quantity' => $newQty,
                    'updated_at' => now(),
                ]
            );
        }

        $invoice->update([
            'stock_deducted_at' => now(),
        ]);
    }

    private function restoreStockForCancelledSalesInvoice(SalesInvoice $invoice): void
    {
        $items = SalesInvoiceItem::query()->where('sales_invoice_id', $invoice->id)->get();
        if ($items->isEmpty()) {
            return;
        }

        foreach ($items as $item) {
            if (!$item->product_id) {
                continue;
            }

            $warehouseId = (int) ($item->warehouse_id ?? 0);
            if ($warehouseId <= 0) {
                throw new \RuntimeException('Készlet visszaállításhoz hiányzik a raktár a számla tételben.');
            }

            $currentQty = (float) (DB::table('product_stocks')
                ->where('warehouse_id', '=', $warehouseId)
                ->where('product_id', '=', (int) $item->product_id)
                ->lockForUpdate()
                ->value('quantity') ?? 0);

            $add = (float) ($item->quantity ?? 0);
            $newQty = $currentQty + $add;

            DB::table('product_stocks')->updateOrInsert(
                [
                    'warehouse_id' => $warehouseId,
                    'product_id' => (int) $item->product_id,
                ],
                [
                    'quantity' => $newQty,
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function payments($id)
    {
        $sales_invoice_payments = SalesInvoicePayment::query()->where('sales_invoice_id', $id)->get();
        return $sales_invoice_payments;
    }

    public function addPayment(Request $request, InvoiceServiceInterface $invoiceService)
    {
        $user = auth('admin')->user();

        /*if (!$user || !$user->can('update-sales-invoice')) {
            return response()->json([
                'message' => 'Nincs jogosultságod a befizetés rögzítéséhez.'
            ], 403);
        }*/

        $validated = $request->validate([
            'sales_invoice_id' => 'required|integer|exists:sales_invoices,id',
            'paid_at' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'payment_method' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        $invoice = SalesInvoice::query()
            ->findOrFail($validated['sales_invoice_id']);

        $company = null;

        if (!empty($invoice->company_id)) {
            $company = Company::query()
                ->where('status', 'active')
                ->find((int) $invoice->company_id);
        }

        if (!$company) {
            return response()->json([
                'message' => 'A számlához nincs érvényes cég rendelve.'
            ], 422);
        }

        // Befizetés előkészítése DB-mentés nélkül
        $payment = new SalesInvoicePayment([
            'paid_at' => $validated['paid_at'],
            'amount' => $validated['amount'],
            'currency' => $validated['currency'] ?? $invoice->currency ?? 'HUF',
            'payment_method' => $validated['payment_method'] ?? null,
            'transaction_id' => $validated['transaction_id'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        // Először a Számlázz.hu-n rögzítjük a befizetést
        try {
            $invoiceService->registerPayment(
                $invoice,
                $payment,
                (string) $company->billing_provider_api_key
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'A befizetés Számlázz.hu-n történő rögzítése sikertelen.',
            ], 502);
        }

        // Csak sikeres Számlázz.hu válasz után módosítjuk az adatbázist
        DB::transaction(function () use ($invoice, $payment) {
            // Számlazárásig vagy más egyidejű befizetésig zároljuk a számlát
            $lockedInvoice = SalesInvoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedInvoice->payments()->save($payment);

            // Összes befizetés újraszámolása
            $paidAmount = $lockedInvoice->payments()->sum('amount');

            // Hátralék
            $outstandingAmount = max(
                0,
                (float) $lockedInvoice->gross_total - (float) $paidAmount
            );

            if ($paidAmount <= 0) {
                $paymentStatus = 'unpaid';
                $settledAt = null;
            } elseif ($outstandingAmount <= 0) {
                $paymentStatus = 'paid';
                $settledAt = now();
            } else {
                $paymentStatus = 'partially_paid';
                $settledAt = null;
            }

            $lockedInvoice->update([
                'paid_amount' => $paidAmount,
                'outstanding_amount' => $outstandingAmount,
                'payment_status' => $paymentStatus,
                'settled_at' => $settledAt,
            ]);
        });

        return response()->json($payment->fresh(), 201);
    }

    public function deletePayment(SalesInvoicePayment $payment, InvoiceServiceInterface $invoiceService) {
        $user = auth('admin')->user();

        /*if (!$user || !$user->can('update-sales-invoice')) {
            return response()->json([
                'message' => 'Nincs jogosultságod a befizetés törléséhez.'
            ], 403);
        }*/

        $invoice = $payment->salesInvoice;

        if (!$invoice) {
            return response()->json([
                'message' => 'A befizetéshez tartozó számla nem található.'
            ], 404);
        }

        // Sajnos nincs lehetőség törölni a szamlazz.hu-n
        /*$company = null;

        if (!empty($invoice->company_id)) {
            $company = Company::query()
                ->where('status', 'active')
                ->find((int) $invoice->company_id);
        }

        if (!$company) {
            return response()->json([
                'message' => 'A számlához nincs érvényes cég rendelve.'
            ], 422);
        }

        // Először a Számlázz.hu-n töröljük a befizetést.
        try {
            $invoiceService->deletePayment(
                $invoice,
                $payment,
                (string) $company->billing_provider_api_key
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'A befizetés törlése a Számlázz.hu rendszerében sikertelen.',
            ], 502);
        }*/

        // Csak sikeres külső törlés után törlünk lokálisan.
        DB::transaction(function () use ($payment, $invoice) {
            $lockedInvoice = SalesInvoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Ellenőrizzük, hogy a befizetés még létezik-e.
            $lockedPayment = $lockedInvoice->payments()
                ->whereKey($payment->id)
                ->first();

            if (!$lockedPayment) {
                return;
            }

            $lockedPayment->delete();

            // A megmaradt befizetések újraszámolása.
            $paidAmount = $lockedInvoice->payments()->sum('amount');

            $outstandingAmount = max(
                0,
                (float) $lockedInvoice->gross_total - (float) $paidAmount
            );

            if ($paidAmount <= 0) {
                $paymentStatus = 'unpaid';
                $settledAt = null;
            } elseif ($outstandingAmount <= 0) {
                $paymentStatus = 'paid';
                $settledAt = now();
            } else {
                $paymentStatus = 'partially_paid';
                $settledAt = null;
            }

            $lockedInvoice->update([
                'paid_amount' => $paidAmount,
                'outstanding_amount' => $outstandingAmount,
                'payment_status' => $paymentStatus,
                'settled_at' => $settledAt,
            ]);
        });

        return response()->json([
            'message' => 'A befizetés sikeresen törölve.'
        ]);
    }
}
