<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Product;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class DeliveryNoteController extends Controller
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

        $warehouses = Warehouse::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return view('admin.documents.delivery-notes', [
            'companies' => $companies,
            'defaultCompanyId' => $defaultCompanyId,
            'warehouses' => $warehouses,
        ]);
    }

    public function data()
    {
        $notes = DeliveryNote::query()
            ->from('delivery_notes as dn')
            ->leftJoin('delivery_note_items as dni', 'dni.delivery_note_id', '=', 'dn.id')
            ->select([
                'dn.id',
                'dn.company_id',
                'dn.document_number',
                'dn.partner_name',
                'dn.issued_at',
                'dn.delivered_at',
                'dn.status',
                'dn.pdf_path',
                'dn.created_at as created',
                'dn.updated_at as updated',
            ])
            ->selectRaw('COALESCE(SUM(ROUND(COALESCE(dni.net_price, 0) * COALESCE(dni.quantity, 0))), 0) as net_total')
            ->selectRaw('COALESCE(SUM(ROUND(COALESCE(dni.net_price, 0) * COALESCE(dni.quantity, 0) * (COALESCE(dni.vat_percent, 0) / 100))), 0) as vat_total')
            ->selectRaw('COALESCE(SUM(ROUND(COALESCE(dni.gross_price, 0) * COALESCE(dni.quantity, 0))), 0) as gross_total')
            ->groupBy([
                'dn.id',
                'dn.company_id',
                'dn.document_number',
                'dn.partner_name',
                'dn.issued_at',
                'dn.delivered_at',
                'dn.status',
                'dn.pdf_path',
                'dn.created_at',
                'dn.updated_at',
            ]);

        return DataTables::of($notes)
            ->addColumn('net_total', function ($note) {
                return number_format((int) ($note->net_total ?? 0), 0, ',', ' ');
            })
            ->addColumn('vat_total', function ($note) {
                return number_format((int) ($note->vat_total ?? 0), 0, ',', ' ');
            })
            ->addColumn('gross_total', function ($note) {
                return number_format((int) ($note->gross_total ?? 0), 0, ',', ' ');
            })
            ->addColumn('action', function ($note) {
                $user = auth('admin')->user();
                $buttons = '';

                if (!empty($note->pdf_path) && $user && $user->can('view-delivery-notes')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-outline-secondary pdf" data-id="' . $note->id . '" title="PDF megnyitása">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                    ';
                }

                if (($note->status ?? null) === 'draft' && $user && $user->can('edit-delivery-note')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $note->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-delivery-note')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $note->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->addColumn('status', function ($delivery) {
                $translations = [
                    'draft'   => 'Piszkozat',
                    'issued'   => 'Kész'
                ];
                return $translations[$delivery->status] ?? ucfirst($delivery->status);
            })
            ->editColumn('issued_at', function ($delivery) {
                return $delivery->issued_at ? $delivery->issued_at->format('Y-m-d') : '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-delivery-note')) {
            return response()->json(['message' => 'Nincs jogosultságod létrehozni.'], 403);
        }

        $validated = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',

            'partnerable_type' => 'nullable|string|max:255',
            'partnerable_id' => 'nullable|integer',

            'document_number' => 'nullable|string|max:255|unique:delivery_notes,document_number',

            'partner_name' => 'required|string|max:255',
            'partner_tax_number' => 'nullable|string|max:255',
            'partner_country' => 'nullable|string|max:2',
            'partner_zip_code' => 'nullable|string|max:255',
            'partner_city' => 'nullable|string|max:255',
            'partner_address_line' => 'nullable|string|max:255',

            'shipping_country' => 'nullable|string|max:2',
            'shipping_zip_code' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_address_line' => 'nullable|string|max:255',

            'issued_at' => 'nullable|date',
            'delivered_at' => 'nullable|date',

            'carrier_name' => 'nullable|string|max:255',
            'vehicle_plate' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'handed_over_at' => 'nullable|date',
            'received_by_name' => 'nullable|string|max:255',

            'note_for_document' => 'nullable|string',
            'note' => 'nullable|string',

            'items_json' => 'nullable|string',
            'company_site_id' => 'nullable|integer|exists:company_sites,id',
        ]);

        // items_json is not a DB column; it's only used to sync document items.
        unset($validated['items_json']);

        $company = Company::query()->where('status', 'active')->find($validated['company_id']);
        if (!$company) {
            return response()->json(['message' => 'Kérlek válassz egy céget.'], 422);
        }

        $warehouse = Warehouse::query()->find($validated['warehouse_id']);
        if (!$warehouse) {
            return response()->json(['message' => 'Kérlek válassz egy raktárat.'], 422);
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

        $warehouseId = (int) $validated['warehouse_id'];
        unset($validated['warehouse_id']);

        $payload = array_merge([
            'status' => 'draft',
            'warehouse_id' => $warehouseId,
        ], $validated);

        $note = DB::transaction(function () use ($payload, $request, $warehouseId) {
            $number = trim((string) ($payload['document_number'] ?? ''));
            $payload['document_number'] = $number !== '' ? $number : 'DRAFT-' . uniqid();

            $note = DeliveryNote::create($payload);

            if (str_starts_with((string) $note->document_number, 'DRAFT-')) {
                $note->update([
                    'document_number' => 'DRAFT-' . $note->id,
                ]);
            }

            $this->syncItemsFromJson($note->id, (string) $request->input('items_json', '[]'));

            return $note;
        });

        return response()->json([
            'message' => 'Sikeres mentés!',
            'delivery_note' => $note,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-delivery-note')) {
            return response()->json(['message' => 'Nincs jogosultságod szerkeszteni.'], 403);
        }

        $note = DeliveryNote::query()->findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',

            'partnerable_type' => 'nullable|string|max:255',
            'partnerable_id' => 'nullable|integer',

            'document_number' => 'nullable|string|max:255|unique:delivery_notes,document_number,' . $note->id,

            'partner_name' => 'required|string|max:255',
            'partner_tax_number' => 'nullable|string|max:255',
            'partner_country' => 'nullable|string|max:2',
            'partner_zip_code' => 'nullable|string|max:255',
            'partner_city' => 'nullable|string|max:255',
            'partner_address_line' => 'nullable|string|max:255',

            'shipping_country' => 'nullable|string|max:2',
            'shipping_zip_code' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_address_line' => 'nullable|string|max:255',

            'issued_at' => 'nullable|date',
            'delivered_at' => 'nullable|date',

            'carrier_name' => 'nullable|string|max:255',
            'vehicle_plate' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'handed_over_at' => 'nullable|date',
            'received_by_name' => 'nullable|string|max:255',

            'note_for_document' => 'nullable|string',
            'note' => 'nullable|string',

            'items_json' => 'nullable|string',
            'company_site_id' => 'nullable|integer|exists:company_sites,id',
        ]);

        // items_json is not a DB column; it's only used to sync document items.
        unset($validated['items_json']);

        $company = Company::query()->where('status', 'active')->find($validated['company_id']);
        if (!$company) {
            return response()->json(['message' => 'Kérlek válassz egy céget.'], 422);
        }

        $warehouse = Warehouse::query()->find($validated['warehouse_id']);
        if (!$warehouse) {
            return response()->json(['message' => 'Kérlek válassz egy raktárat.'], 422);
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

        $warehouseId = (int) $validated['warehouse_id'];

        unset($validated['warehouse_id']);

        $note = DB::transaction(function () use ($note, $validated, $request, $warehouseId) {
            $number = trim((string) ($validated['document_number'] ?? ''));
            if ($number === '') {
                unset($validated['document_number']);
            }

            $validated['warehouse_id'] = $warehouseId;

            $note->update($validated);
            $this->syncItemsFromJson($note->id, (string) $request->input('items_json', '[]'));

            return $note;
        });

        return response()->json([
            'message' => 'Sikeres frissítés!',
            'delivery_note' => $note,
        ], 200);
    }

    public function show($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-delivery-note')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $note = DeliveryNote::query()->with(['items'])->findOrFail($id);

        return response()->json([
            'delivery_note' => $note,
            'items' => $note->items,
        ]);
    }

    public function pdf(int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-delivery-notes')) {
            abort(403);
        }

        $note = DeliveryNote::query()->findOrFail($id);
        $path = (string) ($note->pdf_path ?? '');
        if ($path === '' || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $absolute = storage_path('app/private/' . ltrim($path, '/'));
        if (!file_exists($absolute)) {
            abort(404);
        }

        return response()->file($absolute, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="szallitolevel-' . $note->id . '.pdf"',
        ]);
    }

    public function previewPdf(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-delivery-note')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $validated = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'document_number' => 'nullable|string|max:255',
            'partnerable_type' => 'nullable|string|max:255',
            'partnerable_id' => 'nullable|integer',
            'partner_name' => 'required|string|max:255',
            'partner_tax_number' => 'nullable|string|max:255',
            'partner_country' => 'nullable|string|max:2',
            'partner_zip_code' => 'nullable|string|max:255',
            'partner_city' => 'nullable|string|max:255',
            'partner_address_line' => 'nullable|string|max:255',
            'shipping_country' => 'nullable|string|max:2',
            'shipping_zip_code' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_address_line' => 'nullable|string|max:255',
            'issued_at' => 'nullable|date',
            'delivered_at' => 'nullable|date',
            'carrier_name' => 'nullable|string|max:255',
            'vehicle_plate' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'handed_over_at' => 'nullable|date',
            'received_by_name' => 'nullable|string|max:255',
            'note_for_document' => 'nullable|string',
            'note' => 'nullable|string',
            'items_json' => 'required|string',
            'company_site_id' => 'nullable|integer|exists:company_sites,id',
        ]);

        $items = $this->parseItemsForPdf((string) $validated['items_json']);
        if (count($items) === 0) {
            return response()->json(['message' => 'Nincs tétel a szállítólevélen.'], 422);
        }

        $company = Company::query()->where('status', 'active')->find($validated['company_id']);
        if (!$company) {
            return response()->json(['message' => 'Kérlek válassz egy céget.'], 422);
        }

        $warehouse = Warehouse::query()->find($validated['warehouse_id']);
        if (!$warehouse) {
            return response()->json(['message' => 'Kérlek válassz egy raktárat.'], 422);
        }

        $noteData = $validated;
        $noteData['company_id'] = $company->id;
        $noteData['company_name'] = $company->name;
        $noteData['company_tax_number'] = $company->tax_number;
        $noteData['company_country'] = $company->country;
        $noteData['company_zip_code'] = $company->zip_code;
        $noteData['company_city'] = $company->city;
        $noteData['company_address_line'] = $company->address_line;
        $noteData['company_email'] = $company->email;
        $noteData['company_phone'] = $company->phone;
        $noteData['company_bank_account'] = $company->bank_account;

        unset($noteData['warehouse_id']);

        $note = new DeliveryNote($noteData);

        $pdf = Pdf::loadView('pdf.delivery-note', [
            'delivery_note' => $note,
            'items' => $items,
            'warehouse' => $warehouse,
        ]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="szallitolevel-elonezet.pdf"',
        ]);
    }

    public function issuePdf(Request $request, int $id)
    {
        $user = auth('admin')->user();
        if (!$user || (!$user->can('create-delivery-note') && !$user->can('edit-delivery-note'))) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $note = DeliveryNote::query()->with(['items'])->findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'document_number' => 'nullable|string|max:255',

            'partnerable_type' => 'nullable|string|max:255',
            'partnerable_id' => 'nullable|integer',

            'partner_name' => 'required|string|max:255',
            'partner_tax_number' => 'nullable|string|max:255',
            'partner_country' => 'nullable|string|max:2',
            'partner_zip_code' => 'nullable|string|max:255',
            'partner_city' => 'nullable|string|max:255',
            'partner_address_line' => 'nullable|string|max:255',
            'shipping_country' => 'nullable|string|max:2',
            'shipping_zip_code' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_address_line' => 'nullable|string|max:255',
            'issued_at' => 'nullable|date',
            'delivered_at' => 'nullable|date',
            'carrier_name' => 'nullable|string|max:255',
            'vehicle_plate' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'handed_over_at' => 'nullable|date',
            'received_by_name' => 'nullable|string|max:255',
            'note_for_document' => 'nullable|string',
            'note' => 'nullable|string',
            'items_json' => 'required|string',
        ]);

        $itemsForPdf = $this->parseItemsForPdf((string) $validated['items_json']);
        if (count($itemsForPdf) === 0) {
            return response()->json(['message' => 'Nincs tétel a szállítólevélen.'], 422);
        }

        $warehouseId = (int) $validated['warehouse_id'];

        $pdfBytes = DB::transaction(function () use ($note, $validated, $itemsForPdf, $warehouseId) {
            $note->refresh();

            $warehouse = Warehouse::query()->find($warehouseId);
            if (!$warehouse) {
                throw new \RuntimeException('Kérlek válassz egy raktárat.');
            }

            $validatedForUpdate = $validated;
            unset($validatedForUpdate['warehouse_id']);
            unset($validatedForUpdate['items_json']);

            $company = Company::query()->where('status', 'active')->find($validatedForUpdate['company_id'] ?? null);
            if (!$company) {
                throw new \RuntimeException('Kérlek válassz egy céget.');
            }

            $prefix = strtoupper(trim((string) ($company->prefix ?? '')));
            if ($prefix === '') {
                $fallback = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) ($company->name ?? '')));
                $prefix = substr($fallback, 0, 6);
            }
            if ($prefix === '') {
                $prefix = 'SZL';
            }

            $validatedForUpdate['company_id'] = $company->id;
            $validatedForUpdate['company_name'] = $company->name;
            $validatedForUpdate['company_tax_number'] = $company->tax_number;
            $validatedForUpdate['company_country'] = $company->country;
            $validatedForUpdate['company_zip_code'] = $company->zip_code;
            $validatedForUpdate['company_city'] = $company->city;
            $validatedForUpdate['company_address_line'] = $company->address_line;
            $validatedForUpdate['company_email'] = $company->email;
            $validatedForUpdate['company_phone'] = $company->phone;
            $validatedForUpdate['company_bank_account'] = $company->bank_account;

            $validatedForUpdate['warehouse_id'] = $warehouseId;

            $note->update($validatedForUpdate);
            $this->syncItemsFromJson($note->id, (string) $validated['items_json']);

            $pdf = Pdf::loadView('pdf.delivery-note', [
                'delivery_note' => $note->fresh(),
                'items' => $itemsForPdf,
                'warehouse' => $warehouse,
            ]);

            $bytes = $pdf->output();

            $month = now()->format('Y-m');
            $dir = 'delivery-notes/' . $month;
            $fileName = 'szallitolevel-' . $note->id . '.pdf';
            $relativePath = $dir . '/' . $fileName;

            Storage::disk('local')->put($relativePath, $bytes);

            if ($note->stock_deducted_at === null) {
                $this->deductStockForIssuedDeliveryNote($note->fresh(), $warehouseId);
            }

            $note->update([
                'pdf_path' => $relativePath,
                'status' => 'issued',
                'stock_deducted_at' => $note->stock_deducted_at ?: now(),
            ]);

            if ($note->document_number === null || $note->document_number === '' || str_starts_with((string) $note->document_number, 'DRAFT-')) {
                $note->update([
                    'document_number' => $prefix . '-' . $note->id,
                ]);
            }

            return $bytes;
        });

        $fileName = 'szallitolevel-' . $note->id . '.pdf';

        return response($pdfBytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    public function destroy(int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('delete-delivery-note')) {
            return response()->json(['message' => 'Nincs jogosultságod törölni.'], 403);
        }

        $note = DeliveryNote::query()->with(['items'])->findOrFail($id);

        DB::transaction(function () use ($note) {
            $note->refresh();

            $path = (string) ($note->pdf_path ?? '');
            if ($path !== '' && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }

            if ($note->stock_deducted_at !== null) {
                $warehouseId = (int) ($note->warehouse_id ?? 0);
                if ($warehouseId <= 0) {
                    throw new \RuntimeException('Hiányzó raktár a készlet visszaadáshoz.');
                }

                $items = DeliveryNoteItem::query()->where('delivery_note_id', $note->id)->get();
                $productIds = $items->pluck('product_id')->filter()->unique()->values()->all();

                if (count($productIds) > 0) {
                    $stocks = DB::table('product_stocks')
                        ->where('warehouse_id', '=', $warehouseId)
                        ->whereIn('product_id', $productIds)
                        ->lockForUpdate()
                        ->get(['product_id', 'quantity']);

                    $byProductId = $stocks->keyBy('product_id');

                    foreach ($items as $item) {
                        if (!$item->product_id) {
                            continue;
                        }

                        $currentQty = (float) (($byProductId[$item->product_id]->quantity ?? 0) ?? 0);
                        $addBack = (float) ($item->quantity ?? 0);
                        $newQty = $currentQty + $addBack;

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
            }

            $note->items()->delete();
            $note->delete();
        });

        return response()->json(['message' => 'Sikeres törlés!'], 200);
    }

    private function syncItemsFromJson(int $deliveryNoteId, string $itemsJson): void
    {
        $decoded = json_decode($itemsJson, true);
        if (!is_array($decoded)) {
            return;
        }

        $decoded = array_values(array_filter($decoded, fn ($row) => is_array($row)));

        DeliveryNoteItem::query()->where('delivery_note_id', $deliveryNoteId)->delete();

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

            $netPrice = $row['net_price'] ?? null;
            if ($netPrice !== null && $netPrice !== '' && !is_numeric($netPrice)) {
                $netPrice = null;
            }

            $vatPercent = $row['vat_percent'] ?? null;
            if ($vatPercent !== null && $vatPercent !== '' && !is_numeric($vatPercent)) {
                $vatPercent = null;
            }

            $grossPrice = $row['gross_price'] ?? null;
            if ($grossPrice !== null && $grossPrice !== '' && !is_numeric($grossPrice)) {
                $grossPrice = null;
            }

            DeliveryNoteItem::create([
                'delivery_note_id' => $deliveryNoteId,
                'product_id' => $productId !== null ? (int) $productId : null,
                'sort_order' => $sort,
                'name' => (string) ($row['name'] ?? ''),
                'sku' => isset($row['sku']) ? (string) $row['sku'] : null,
                'unit' => isset($row['unit']) ? (string) $row['unit'] : null,
                'quantity' => (float) $quantity,
                'net_price' => $netPrice !== null ? (float) $netPrice : null,
                'vat_percent' => $vatPercent !== null ? (float) $vatPercent : null,
                'gross_price' => $grossPrice !== null ? (float) $grossPrice : null,
                'note' => isset($row['note']) ? (string) $row['note'] : null,
            ]);

            $sort++;
        }
    }

    private function parseItemsForPdf(string $itemsJson): array
    {
        $decoded = json_decode($itemsJson, true);
        if (!is_array($decoded)) {
            return [];
        }

        $decoded = array_values(array_filter($decoded, fn ($row) => is_array($row)));

        $items = [];
        foreach ($decoded as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $qty = (float) ($row['quantity'] ?? 0);
            if ($name === '' || $qty <= 0) {
                continue;
            }

            $items[] = [
                'product_id' => isset($row['product_id']) && is_numeric($row['product_id']) ? (int) $row['product_id'] : null,
                'name' => $name,
                'sku' => (string) ($row['sku'] ?? ''),
                'unit' => (string) ($row['unit'] ?? 'db'),
                'quantity' => $qty,
                'net_price' => isset($row['net_price']) && is_numeric($row['net_price']) ? (float) $row['net_price'] : null,
                'vat_percent' => isset($row['vat_percent']) && is_numeric($row['vat_percent']) ? (float) $row['vat_percent'] : null,
                'gross_price' => isset($row['gross_price']) && is_numeric($row['gross_price']) ? (float) $row['gross_price'] : null,
                'note' => (string) ($row['note'] ?? ''),
            ];
        }

        return $items;
    }

    private function deductStockForIssuedDeliveryNote(DeliveryNote $note, int $warehouseId): void
    {
        if ($warehouseId <= 0) {
            throw new \RuntimeException('Hiányzó raktár a készlet csökkentéshez.');
        }

        $items = DeliveryNoteItem::query()->where('delivery_note_id', $note->id)->get();
        if ($items->isEmpty()) {
            return;
        }

        $productIds = $items->pluck('product_id')->filter()->unique()->values()->all();
        if (count($productIds) === 0) {
            return;
        }

        $stocks = DB::table('product_stocks')
            ->where('warehouse_id', '=', $warehouseId)
            ->whereIn('product_id', $productIds)
            ->lockForUpdate()
            ->get(['product_id', 'quantity']);

        $byProductId = $stocks->keyBy('product_id');

        foreach ($items as $item) {
            if (!$item->product_id) {
                continue;
            }

            $currentQty = (float) (($byProductId[$item->product_id]->quantity ?? 0) ?? 0);
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

        $note->update([
            'stock_deducted_at' => now(),
        ]);
    }

    private function generateAndStorePdf(DeliveryNote $note, int $warehouseId, array $itemsForPdf): string
    {
        if ($warehouseId <= 0) {
            throw new \RuntimeException('Hiányzó raktár a PDF generáláshoz.');
        }

        $warehouse = Warehouse::query()->find($warehouseId);
        if (!$warehouse) {
            throw new \RuntimeException('Kérlek válassz egy raktárat.');
        }

        $pdf = Pdf::loadView('pdf.delivery-note', [
            'delivery_note' => $note,
            'items' => $itemsForPdf,
            'warehouse' => $warehouse,
        ]);

        $bytes = $pdf->output();

        $month = now()->format('Y-m');
        $dir = 'delivery-notes/' . $month;
        $fileName = 'szallitolevel-' . $note->id . '.pdf';
        $relativePath = $dir . '/' . $fileName;

        $oldPath = (string) ($note->pdf_path ?? '');
        if ($oldPath !== '' && $oldPath !== $relativePath && Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        Storage::disk('local')->put($relativePath, $bytes);

        return $relativePath;
    }
}
