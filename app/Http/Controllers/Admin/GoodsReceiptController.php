<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class GoodsReceiptController extends Controller
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

        return view('admin.documents.goods-receipts', [
            'companies' => $companies,
            'defaultCompanyId' => $defaultCompanyId,
            'warehouses' => $warehouses,
        ]);
    }

    public function data()
    {
        $receipts = GoodsReceipt::query()
            ->from('goods_receipts as gr')
            ->leftJoin('goods_receipt_items as gri', 'gri.goods_receipt_id', '=', 'gr.id')
            ->select([
                'gr.id',
                'gr.company_id',
                'gr.document_number',
                'gr.supplier_document_number',
                'gr.partner_name',
                'gr.received_at',
                'gr.status',
                'gr.pdf_path',
                'gr.stock_added_at',
                'gr.created_at as created',
                'gr.updated_at as updated',
            ])
            ->selectRaw('COALESCE(SUM(ROUND(COALESCE(gri.unit_net_price, 0) * COALESCE(gri.quantity, 0))), 0) as net_total')
            ->selectRaw('COALESCE(SUM(ROUND(COALESCE(gri.unit_net_price, 0) * COALESCE(gri.quantity, 0) * (COALESCE(gri.vat_percent, 0) / 100))), 0) as vat_total')
            ->selectRaw('COALESCE(SUM(ROUND(COALESCE(gri.unit_net_price, 0) * COALESCE(gri.quantity, 0) * (1 + (COALESCE(gri.vat_percent, 0) / 100)))), 0) as gross_total')
            ->groupBy([
                'gr.id',
                'gr.company_id',
                'gr.document_number',
                'gr.supplier_document_number',
                'gr.partner_name',
                'gr.received_at',
                'gr.status',
                'gr.pdf_path',
                'gr.stock_added_at',
                'gr.created_at',
                'gr.updated_at',
            ]);

        return DataTables::of($receipts)
            ->addColumn('net_total', function ($receipt) {
                return number_format((int) ($receipt->net_total ?? 0), 0, ',', ' ');
            })
            ->addColumn('vat_total', function ($receipt) {
                return number_format((int) ($receipt->vat_total ?? 0), 0, ',', ' ');
            })
            ->addColumn('gross_total', function ($receipt) {
                return number_format((int) ($receipt->gross_total ?? 0), 0, ',', ' ');
            })
            ->addColumn('action', function ($receipt) {
                $user = auth('admin')->user();
                $buttons = '';

                if (!empty($receipt->pdf_path) && $user && $user->can('view-goods-receipts')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-outline-secondary pdf" data-id="' . $receipt->id . '" title="PDF megnyitása">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                    ';
                }

                if (($receipt->status ?? null) === 'draft' && $user && $user->can('edit-goods-receipt')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $receipt->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-goods-receipt')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $receipt->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->addColumn('status', function ($receipt) {
                $translations = [
                    'draft'   => 'Piszkozat',
                    'posted'   => 'Kész'
                ];
                return $translations[$receipt->status] ?? ucfirst($receipt->status);
            })
            ->editColumn('received_at', function ($receipt) {
                return $receipt->received_at ? $receipt->received_at->format('Y-m-d') : '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-goods-receipt')) {
            return response()->json(['message' => 'Nincs jogosultságod létrehozni.'], 403);
        }

        $validated = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',

            'document_number' => 'nullable|string|max:255|unique:goods_receipts,document_number',
            'supplier_document_number' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',

            'partner_name' => 'required|string|max:255',
            'partner_tax_number' => 'nullable|string|max:255',
            'partner_country' => 'nullable|string|max:2',
            'partner_zip_code' => 'nullable|string|max:255',
            'partner_city' => 'nullable|string|max:255',
            'partner_address_line' => 'nullable|string|max:255',

            'received_at' => 'nullable|date',

            'note_for_document' => 'nullable|string',
            'note' => 'nullable|string',

            'items_json' => 'nullable|string',
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
        ], $validated);

        $receipt = DB::transaction(function () use ($payload, $request, $warehouseId, $user) {
            $number = trim((string) ($payload['document_number'] ?? ''));
            $payload['document_number'] = $number !== '' ? $number : 'DRAFT-' . uniqid();

            $payload['received_by_user_id'] = $user?->id;
            $payload['warehouse_id'] = $warehouseId;

            $receipt = GoodsReceipt::create($payload);

            if (str_starts_with((string) $receipt->document_number, 'DRAFT-')) {
                $receipt->update([
                    'document_number' => 'DRAFT-' . $receipt->id,
                ]);
            }

            $this->syncItemsFromJson($receipt->id, (string) $request->input('items_json', '[]'));

            return $receipt;
        });

        return response()->json([
            'message' => 'Sikeres mentés!',
            'goods_receipt' => $receipt,
        ], 200);
    }

    public function update(Request $request, int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-goods-receipt')) {
            return response()->json(['message' => 'Nincs jogosultságod szerkeszteni.'], 403);
        }

        $receipt = GoodsReceipt::query()->findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',

            'document_number' => 'nullable|string|max:255|unique:goods_receipts,document_number,' . $receipt->id,
            'supplier_document_number' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',

            'partner_name' => 'required|string|max:255',
            'partner_tax_number' => 'nullable|string|max:255',
            'partner_country' => 'nullable|string|max:2',
            'partner_zip_code' => 'nullable|string|max:255',
            'partner_city' => 'nullable|string|max:255',
            'partner_address_line' => 'nullable|string|max:255',

            'received_at' => 'nullable|date',

            'note_for_document' => 'nullable|string',
            'note' => 'nullable|string',

            'items_json' => 'nullable|string',
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

        $receipt = DB::transaction(function () use ($receipt, $validated, $request, $warehouseId) {
            $number = trim((string) ($validated['document_number'] ?? ''));
            if ($number === '') {
                unset($validated['document_number']);
            }

            $validated['warehouse_id'] = $warehouseId;

            $receipt->update($validated);
            $this->syncItemsFromJson($receipt->id, (string) $request->input('items_json', '[]'));

            return $receipt;
        });

        return response()->json([
            'message' => 'Sikeres frissítés!',
            'goods_receipt' => $receipt,
        ], 200);
    }

    public function show(int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-goods-receipt')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $receipt = GoodsReceipt::query()->with(['items'])->findOrFail($id);

        return response()->json([
            'goods_receipt' => $receipt,
            'items' => $receipt->items,
        ]);
    }

    public function pdf(int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-goods-receipts')) {
            abort(403);
        }

        $receipt = GoodsReceipt::query()->findOrFail($id);
        $path = (string) ($receipt->pdf_path ?? '');
        if ($path === '' || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $absolute = storage_path('app/private/' . ltrim($path, '/'));
        if (!file_exists($absolute)) {
            abort(404);
        }

        return response()->file($absolute, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="bevetelezes-' . $receipt->id . '.pdf"',
        ]);
    }

    public function previewPdf(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-goods-receipt')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'company_id' => 'required|integer|exists:companies,id',
                'warehouse_id' => 'required|integer|exists:warehouses,id',
                'document_number' => 'nullable|string|max:255',
                'supplier_document_number' => 'nullable|string|max:255',

                'partner_name' => 'required|string|max:255',
                'partner_tax_number' => 'nullable|string|max:255',
                'partner_country' => 'nullable|string|max:2',
                'partner_zip_code' => 'nullable|string|max:255',
                'partner_city' => 'nullable|string|max:255',
                'partner_address_line' => 'nullable|string|max:255',

                'received_at' => 'nullable|date',

                'note_for_document' => 'nullable|string',
                'note' => 'nullable|string',

                'items_json' => 'required|string',
            ],
            [
                'company_id' => 'cég',
                'warehouse_id' => 'raktár',
                'document_number' => 'bizonylatszám',
                'supplier_document_number' => 'szállítói bizonylatszám',

                'partner_name' => 'partner neve',
                'partner_tax_number' => 'partner adószáma',
                'partner_country' => 'partner országa',
                'partner_zip_code' => 'partner irányítószáma',
                'partner_city' => 'partner települése',
                'partner_address_line' => 'partner címe',

                'received_at' => 'bevételezés dátuma',

                'note_for_document' => 'bizonylat megjegyzése',
                'note' => 'megjegyzés',

                'items_json' => 'tételek',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validációs hiba.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $items = $this->parseItemsForPdf((string) $validated['items_json']);
        if (count($items) === 0) {
            return response()->json(['message' => 'Nincs tétel a bevételezésen.'], 422);
        }

        $company = Company::query()->where('status', 'active')->find($validated['company_id']);
        if (!$company) {
            return response()->json(['message' => 'Kérlek válassz egy céget.'], 422);
        }

        $warehouse = Warehouse::query()->find($validated['warehouse_id']);
        if (!$warehouse) {
            return response()->json(['message' => 'Kérlek válassz egy raktárat.'], 422);
        }

        $receiptData = $validated;
        $receiptData['company_id'] = $company->id;
        $receiptData['company_name'] = $company->name;
        $receiptData['company_tax_number'] = $company->tax_number;
        $receiptData['company_country'] = $company->country;
        $receiptData['company_zip_code'] = $company->zip_code;
        $receiptData['company_city'] = $company->city;
        $receiptData['company_address_line'] = $company->address_line;
        $receiptData['company_email'] = $company->email;
        $receiptData['company_phone'] = $company->phone;
        $receiptData['company_bank_account'] = $company->bank_account;

        unset($receiptData['warehouse_id']);

        $receipt = new GoodsReceipt($receiptData);

        $pdf = Pdf::loadView('pdf.goods-receipt', [
            'goods_receipt' => $receipt,
            'items' => $items,
            'warehouse' => $warehouse,
        ]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="bevetelezes-elonezet.pdf"',
        ]);
    }

    public function issuePdf(Request $request, int $id)
    {
        $user = auth('admin')->user();
        if (!$user || (!$user->can('create-goods-receipt') && !$user->can('edit-goods-receipt'))) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $receipt = GoodsReceipt::query()->with(['items'])->findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'document_number' => 'nullable|string|max:255',
            'supplier_document_number' => 'nullable|string|max:255',

            'partner_name' => 'required|string|max:255',
            'partner_tax_number' => 'nullable|string|max:255',
            'partner_country' => 'nullable|string|max:2',
            'partner_zip_code' => 'nullable|string|max:255',
            'partner_city' => 'nullable|string|max:255',
            'partner_address_line' => 'nullable|string|max:255',

            'received_at' => 'nullable|date',

            'note_for_document' => 'nullable|string',
            'note' => 'nullable|string',

            'items_json' => 'required|string',
        ]);

        $itemsForPdf = $this->parseItemsForPdf((string) $validated['items_json']);
        if (count($itemsForPdf) === 0) {
            return response()->json(['message' => 'Nincs tétel a bevételezésen.'], 422);
        }

        $warehouseId = (int) $validated['warehouse_id'];

        $pdfBytes = DB::transaction(function () use ($receipt, $validated, $itemsForPdf, $warehouseId) {
            $receipt->refresh();

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
                $prefix = 'BEV';
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

            $receipt->update($validatedForUpdate);
            $this->syncItemsFromJson($receipt->id, (string) $validated['items_json']);

            $pdf = Pdf::loadView('pdf.goods-receipt', [
                'goods_receipt' => $receipt->fresh(),
                'items' => $itemsForPdf,
                'warehouse' => $warehouse,
            ]);

            $bytes = $pdf->output();

            $month = now()->format('Y-m');
            $dir = 'goods-receipts/' . $month;
            $fileName = 'bevetelezes-' . $receipt->id . '.pdf';
            $relativePath = $dir . '/' . $fileName;

            Storage::disk('local')->put($relativePath, $bytes);

            if ($receipt->stock_added_at === null) {
                $this->addStockForIssuedGoodsReceipt($receipt->fresh(), $warehouseId);
            }

            $receipt->update([
                'pdf_path' => $relativePath,
                'status' => 'posted',
                'stock_added_at' => $receipt->stock_added_at ?: now(),
            ]);

            if ($receipt->document_number === null || $receipt->document_number === '' || str_starts_with((string) $receipt->document_number, 'DRAFT-')) {
                $receipt->update([
                    'document_number' => $prefix . '-' . $receipt->id,
                ]);
            }

            return $bytes;
        });

        $fileName = 'bevetelezes-' . $receipt->id . '.pdf';

        return response($pdfBytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    public function destroy(int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('delete-goods-receipt')) {
            return response()->json(['message' => 'Nincs jogosultságod törölni.'], 403);
        }

        $receipt = GoodsReceipt::query()->with(['items'])->findOrFail($id);

        DB::transaction(function () use ($receipt) {
            $receipt->refresh();

            if ($receipt->stock_added_at !== null) {
                $warehouseId = (int) ($receipt->warehouse_id ?? 0);
                if ($warehouseId <= 0) {
                    throw new \RuntimeException('Hiányzó raktár a törléshez.');
                }

                $items = GoodsReceiptItem::query()->where('goods_receipt_id', $receipt->id)->get();
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
                        $sub = (float) ($item->quantity ?? 0);
                        $newQty = $currentQty - $sub;

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

            $path = (string) ($receipt->pdf_path ?? '');
            if ($path !== '' && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }

            $receipt->items()->delete();
            $receipt->delete();
        });

        return response()->json(['message' => 'Sikeres törlés!'], 200);
    }

    private function syncItemsFromJson(int $goodsReceiptId, string $itemsJson): void
    {
        $decoded = json_decode($itemsJson, true);
        if (!is_array($decoded)) {
            return;
        }

        $decoded = array_values(array_filter($decoded, fn ($row) => is_array($row)));

        GoodsReceiptItem::query()->where('goods_receipt_id', $goodsReceiptId)->delete();

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

            $unitNetPrice = $row['unit_net_price'] ?? null;
            if ($unitNetPrice !== null && $unitNetPrice !== '' && !is_numeric($unitNetPrice)) {
                $unitNetPrice = null;
            }

            $unitGrossPrice = $row['unit_gross_price'] ?? null;
            if ($unitGrossPrice !== null && $unitGrossPrice !== '' && !is_numeric($unitGrossPrice)) {
                $unitGrossPrice = null;
            }

            $vatPercent = $row['vat_percent'] ?? null;
            if ($vatPercent !== null && $vatPercent !== '' && !is_numeric($vatPercent)) {
                $vatPercent = null;
            }

            GoodsReceiptItem::create([
                'goods_receipt_id' => $goodsReceiptId,
                'product_id' => $productId !== null ? (int) $productId : null,
                'sort_order' => $sort,
                'name' => (string) ($row['name'] ?? ''),
                'sku' => isset($row['sku']) ? (string) $row['sku'] : null,
                'unit' => isset($row['unit']) ? (string) $row['unit'] : null,
                'quantity' => (float) $quantity,
                'unit_net_price' => $unitNetPrice !== null ? (int) $unitNetPrice : null,
                'unit_gross_price' => $unitGrossPrice !== null ? (int) $unitGrossPrice : null,
                'vat_percent' => $vatPercent !== null ? (int) $vatPercent : null,
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

            $unitNetPrice = isset($row['unit_net_price']) && is_numeric($row['unit_net_price']) ? (int) $row['unit_net_price'] : 0;
            $vatPercent = isset($row['vat_percent']) && is_numeric($row['vat_percent']) ? (int) $row['vat_percent'] : 0;

            $net = (int) round($unitNetPrice * $qty);
            $vat = (int) round($net * ($vatPercent / 100));
            $gross = $net + $vat;

            $items[] = [
                'product_id' => isset($row['product_id']) && is_numeric($row['product_id']) ? (int) $row['product_id'] : null,
                'name' => $name,
                'sku' => (string) ($row['sku'] ?? ''),
                'unit' => (string) ($row['unit'] ?? 'db'),
                'quantity' => $qty,
                'unit_net_price' => $unitNetPrice,
                'vat_percent' => $vatPercent,
                'net_value' => $net,
                'vat_value' => $vat,
                'gross_value' => $gross,
                'note' => (string) ($row['note'] ?? ''),
            ];
        }

        return $items;
    }

    private function addStockForIssuedGoodsReceipt(GoodsReceipt $receipt, int $warehouseId): void
    {
        if ($warehouseId <= 0) {
            throw new \RuntimeException('Hiányzó raktár a készlet növeléshez.');
        }

        $items = GoodsReceiptItem::query()->where('goods_receipt_id', $receipt->id)->get();
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

        $receipt->update([
            'stock_added_at' => now(),
        ]);
    }

    private function generateAndStorePdf(GoodsReceipt $receipt, array $itemsForPdf): string
    {
        $warehouseId = (int) ($receipt->warehouse_id ?? 0);
        if ($warehouseId <= 0) {
            throw new \RuntimeException('Hiányzó raktár a PDF generáláshoz.');
        }

        $warehouse = Warehouse::query()->find($warehouseId);
        if (!$warehouse) {
            throw new \RuntimeException('Kérlek válassz egy raktárat.');
        }

        $pdf = Pdf::loadView('pdf.goods-receipt', [
            'goods_receipt' => $receipt,
            'items' => $itemsForPdf,
            'warehouse' => $warehouse,
        ]);

        $bytes = $pdf->output();

        $month = now()->format('Y-m');
        $dir = 'goods-receipts/' . $month;
        $fileName = 'bevetelezes-' . $receipt->id . '.pdf';
        $relativePath = $dir . '/' . $fileName;

        $oldPath = (string) ($receipt->pdf_path ?? '');
        if ($oldPath !== '' && $oldPath !== $relativePath && Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        Storage::disk('local')->put($relativePath, $bytes);

        return $relativePath;
    }
}
