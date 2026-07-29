<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class WarehouseTransferController extends Controller
{
    public function index()
    {
        $user = auth('admin')->user();
        abort_unless($user && $user->can('view-warehouse-transfers'), 403);

        $warehouses = Warehouse::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'country',
                'zip_code',
                'city',
                'address_line',
            ]);

        return view('admin.warehouse.warehouse-transfers', [
            'warehouses' => $warehouses,
        ]);
    }

    private function ensureEnoughStockForTransfer(int $transferId, int $fromWarehouseId): void
    {
        if ($fromWarehouseId <= 0) {
            throw new \RuntimeException('Hiányzó forrás raktár a készlet ellenőrzéshez.');
        }

        $items = WarehouseTransferItem::query()->where('warehouse_transfer_id', $transferId)->get();
        if ($items->isEmpty()) {
            return;
        }

        $productIds = $items->pluck('product_id')->filter()->unique()->values()->all();
        if (count($productIds) === 0) {
            return;
        }

        $stocks = DB::table('product_stocks')
            ->where('warehouse_id', '=', $fromWarehouseId)
            ->whereIn('product_id', $productIds)
            ->lockForUpdate()
            ->get(['product_id', 'quantity']);

        $byProduct = $stocks->keyBy('product_id');

        foreach ($items as $item) {
            if (!$item->product_id) {
                continue;
            }

            $need = (float) ($item->quantity ?? 0);
            if ($need <= 0) {
                continue;
            }

            $available = (float) (($byProduct[$item->product_id]->quantity ?? 0) ?? 0);
            if ($available < $need) {
                throw new \RuntimeException('Nincs elegendő készlet a forrás raktárban: ' . (string) ($item->name ?? ''));
            }
        }
    }

    public function data()
    {
        $user = auth('admin')->user();
        abort_unless($user && $user->can('view-warehouse-transfers'), 403);

        $transfers = WarehouseTransfer::query()
            ->leftJoin('warehouses as w_from', 'w_from.id', '=', 'warehouse_transfers.from_warehouse_id')
            ->leftJoin('warehouses as w_to', 'w_to.id', '=', 'warehouse_transfers.to_warehouse_id')
            ->select([
                'warehouse_transfers.id',
                'warehouse_transfers.document_number',
                'warehouse_transfers.from_warehouse_id',
                'warehouse_transfers.to_warehouse_id',
                'warehouse_transfers.transferred_at',
                'warehouse_transfers.status',
                'warehouse_transfers.pdf_path',
                'warehouse_transfers.created_at as created',
                'warehouse_transfers.updated_at as updated',
                DB::raw('COALESCE(w_from.name, "") as from_warehouse'),
                DB::raw('COALESCE(w_to.name, "") as to_warehouse'),
            ]);

        return DataTables::of($transfers)
            ->addColumn('action', function ($transfer) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-warehouse-transfer')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $transfer->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-warehouse-transfer')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $transfer->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
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
        if (!$user || !$user->can('create-warehouse-transfer')) {
            return response()->json(['message' => 'Nincs jogosultságod létrehozni.'], 403);
        }

        $validated = $this->validatePayload($request, null);

        $fromWarehouseId = (int) ($validated['from_warehouse_id'] ?? 0);
        $toWarehouseId = (int) ($validated['to_warehouse_id'] ?? 0);
        if ($fromWarehouseId === $toWarehouseId) {
            return response()->json(['message' => 'A forrás és cél raktár nem lehet azonos.'], 422);
        }

        $fromWarehouse = Warehouse::query()->find($fromWarehouseId);
        $toWarehouse = Warehouse::query()->find($toWarehouseId);
        if (!$fromWarehouse || !$toWarehouse) {
            return response()->json(['message' => 'Kérlek válassz raktárakat.'], 422);
        }

        $payload = array_merge([
            'status' => 'draft',
        ], $validated);

        $transfer = DB::transaction(function () use ($payload, $request, $fromWarehouseId) {
            $number = trim((string) ($payload['document_number'] ?? ''));
            $payload['document_number'] = $number !== '' ? $number : 'DRAFT-' . uniqid();

            $transfer = WarehouseTransfer::create($payload);

            if (str_starts_with((string) $transfer->document_number, 'DRAFT-')) {
                $transfer->update([
                    'document_number' => 'DRAFT-' . $transfer->id,
                ]);
            }

            $this->syncItemsFromJson($transfer->id, (string) $request->input('items_json', '[]'));

            $this->ensureEnoughStockForTransfer($transfer->id, $fromWarehouseId);

            $itemsForPdf = $this->parseItemsForPdf((string) $request->input('items_json', '[]'));
            $relativePath = $this->generateAndStorePdf($transfer->fresh(), $itemsForPdf);
            $transfer->update([
                'pdf_path' => $relativePath,
            ]);

            return $transfer;
        });

        return response()->json([
            'message' => 'Sikeres mentés!',
            'warehouse_transfer' => $transfer,
        ], 200);
    }

    public function update(Request $request, int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-warehouse-transfer')) {
            return response()->json(['message' => 'Nincs jogosultságod szerkeszteni.'], 403);
        }

        $transfer = WarehouseTransfer::query()->findOrFail($id);

        $validated = $this->validatePayload($request, $transfer->id);

        $fromWarehouseId = (int) ($validated['from_warehouse_id'] ?? 0);
        $toWarehouseId = (int) ($validated['to_warehouse_id'] ?? 0);
        if ($fromWarehouseId === $toWarehouseId) {
            return response()->json(['message' => 'A forrás és cél raktár nem lehet azonos.'], 422);
        }

        $fromWarehouse = Warehouse::query()->find($fromWarehouseId);
        $toWarehouse = Warehouse::query()->find($toWarehouseId);
        if (!$fromWarehouse || !$toWarehouse) {
            return response()->json(['message' => 'Kérlek válassz raktárakat.'], 422);
        }

        $transfer = DB::transaction(function () use ($transfer, $validated, $request, $fromWarehouseId, $toWarehouseId) {
            $number = trim((string) ($validated['document_number'] ?? ''));
            if ($number === '') {
                unset($validated['document_number']);
            }

            $transfer->update($validated);

            $this->syncItemsFromJson($transfer->id, (string) $request->input('items_json', '[]'));

            $this->ensureEnoughStockForTransfer($transfer->id, $fromWarehouseId);

            $itemsForPdf = $this->parseItemsForPdf((string) $request->input('items_json', '[]'));
            $relativePath = $this->generateAndStorePdf($transfer->fresh(), $itemsForPdf);
            $transfer->update([
                'pdf_path' => $relativePath,
            ]);

            return $transfer;
        });

        return response()->json([
            'message' => 'Sikeres frissítés!',
            'warehouse_transfer' => $transfer,
        ], 200);
    }

    public function show(int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-warehouse-transfer')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $transfer = WarehouseTransfer::query()->with(['items'])->findOrFail($id);

        return response()->json([
            'warehouse_transfer' => $transfer,
            'items' => $transfer->items,
        ]);
    }

    public function pdf(int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-warehouse-transfers')) {
            abort(403);
        }

        $transfer = WarehouseTransfer::query()->findOrFail($id);
        $path = (string) ($transfer->pdf_path ?? '');
        if ($path === '' || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $absolute = storage_path('app/' . ltrim($path, '/'));
        if (!file_exists($absolute)) {
            abort(404);
        }

        return response()->file($absolute, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="atvezetes-' . $transfer->id . '.pdf"',
        ]);
    }

    public function previewPdf(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-warehouse-transfer')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $createdBy = (string) ($user->name ?? $user->email ?? '');

        $validated = $this->validatePayload($request, null, true);

        $items = $this->parseItemsForPdf((string) $validated['items_json']);
        if (count($items) === 0) {
            return response()->json(['message' => 'Nincs tétel az átvezetésen.'], 422);
        }
        $fromWarehouse = Warehouse::query()->find($validated['from_warehouse_id']);
        $toWarehouse = Warehouse::query()->find($validated['to_warehouse_id']);
        if (!$fromWarehouse || !$toWarehouse) {
            return response()->json(['message' => 'Kérlek válassz raktárakat.'], 422);
        }

        if ((int) $fromWarehouse->id === (int) $toWarehouse->id) {
            return response()->json(['message' => 'A forrás és cél raktár nem lehet azonos.'], 422);
        }

        $transfer = new WarehouseTransfer($validated);

        $pdf = Pdf::loadView('pdf.warehouse-transfer', [
            'warehouse_transfer' => $transfer,
            'items' => $items,
            'fromWarehouse' => $fromWarehouse,
            'toWarehouse' => $toWarehouse,
            'createdBy' => $createdBy,
        ]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="raktarkozi-atvezetes-elonezet.pdf"',
        ]);
    }

    public function issuePdf(Request $request, int $id)
    {
        $user = auth('admin')->user();
        if (!$user || (!$user->can('create-warehouse-transfer') && !$user->can('edit-warehouse-transfer'))) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $createdBy = (string) ($user->name ?? $user->email ?? '');

        $transfer = WarehouseTransfer::query()->with(['items'])->findOrFail($id);

        $validated = $this->validatePayload($request, $transfer->id, true);

        $itemsForPdf = $this->parseItemsForPdf((string) $validated['items_json']);
        if (count($itemsForPdf) === 0) {
            return response()->json(['message' => 'Nincs tétel az átvezetésen.'], 422);
        }

        $pdfBytes = DB::transaction(function () use ($transfer, $validated, $itemsForPdf, $createdBy) {
            $transfer->refresh();

            $fromWarehouseId = (int) ($validated['from_warehouse_id'] ?? 0);
            $toWarehouseId = (int) ($validated['to_warehouse_id'] ?? 0);

            $fromWarehouse = Warehouse::query()->find($fromWarehouseId);
            $toWarehouse = Warehouse::query()->find($toWarehouseId);
            if (!$fromWarehouse || !$toWarehouse) {
                throw new \RuntimeException('Kérlek válassz raktárakat.');
            }

            if ($fromWarehouseId === $toWarehouseId) {
                throw new \RuntimeException('A forrás és cél raktár nem lehet azonos.');
            }

            $transfer->update($validated);
            $this->syncItemsFromJson($transfer->id, (string) $validated['items_json']);

            $this->ensureEnoughStockForTransfer($transfer->id, $fromWarehouseId);

            $pdf = Pdf::loadView('pdf.warehouse-transfer', [
                'warehouse_transfer' => $transfer->fresh(),
                'items' => $itemsForPdf,
                'fromWarehouse' => $fromWarehouse,
                'toWarehouse' => $toWarehouse,
                'createdBy' => $createdBy,
            ]);

            $bytes = $pdf->output();

            $month = now()->format('Y-m');
            $dir = 'private/warehouse-transfers/' . $month;
            $fileName = 'raktarkozi-atvezetes-' . $transfer->id . '.pdf';
            $relativePath = $dir . '/' . $fileName;

            Storage::disk('local')->put($relativePath, $bytes);

            if ($transfer->stock_moved_at === null) {
                $this->moveStockForIssuedTransfer($transfer->fresh(), $fromWarehouseId, $toWarehouseId);
            }

            $transfer->update([
                'pdf_path' => $relativePath,
                'status' => 'posted',
                'stock_moved_at' => $transfer->stock_moved_at ?: now(),
            ]);

            return $bytes;
        });

        $fileName = 'raktarkozi-atvezetes-' . $transfer->id . '.pdf';

        return response($pdfBytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    public function destroy(int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('delete-warehouse-transfer')) {
            return response()->json(['message' => 'Nincs jogosultságod törölni.'], 403);
        }

        $transfer = WarehouseTransfer::query()->with(['items'])->findOrFail($id);

        if ($transfer->stock_moved_at !== null) {
            return response()->json([
                'message' => 'A könyvelt (készletet átvezető) átvezetés nem törölhető.',
            ], 422);
        }

        DB::transaction(function () use ($transfer) {
            $path = (string) ($transfer->pdf_path ?? '');
            if ($path !== '' && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }

            $transfer->items()->delete();
            $transfer->delete();
        });

        return response()->json(['message' => 'Sikeres törlés!'], 200);
    }

    private function validatePayload(Request $request, ?int $transferId = null, bool $requireItemsJson = false): array
    {
        $docRule = 'nullable|string|max:255|unique:warehouse_transfers,document_number';
        if ($transferId) {
            $docRule .= ',' . $transferId;
        }

        $validated = $request->validate([
            'from_warehouse_id' => 'required|integer|exists:warehouses,id',
            'to_warehouse_id' => 'required|integer|exists:warehouses,id',
            'document_number' => $docRule,
            'transferred_at' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'note_before_items' => 'nullable|string',
            'note_after_items' => 'nullable|string',
            'note' => 'nullable|string',
            'items_json' => ($requireItemsJson ? 'required' : 'nullable') . '|string',
        ]);

        // items_json is not a DB column; it's only used to sync document items.
        unset($validated['items_json']);

        return $validated;
    }

    private function syncItemsFromJson(int $transferId, string $itemsJson): void
    {
        $decoded = json_decode($itemsJson, true);
        if (!is_array($decoded)) {
            return;
        }

        $decoded = array_values(array_filter($decoded, fn ($row) => is_array($row)));

        WarehouseTransferItem::query()->where('warehouse_transfer_id', $transferId)->delete();

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

            WarehouseTransferItem::create([
                'warehouse_transfer_id' => $transferId,
                'product_id' => $productId !== null ? (int) $productId : null,
                'sort_order' => $sort,
                'name' => (string) ($row['name'] ?? ''),
                'sku' => isset($row['sku']) ? (string) $row['sku'] : null,
                'unit' => isset($row['unit']) ? (string) $row['unit'] : null,
                'quantity' => (float) $quantity,
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
                'note' => (string) ($row['note'] ?? ''),
            ];
        }

        return $items;
    }

    private function moveStockForIssuedTransfer(WarehouseTransfer $transfer, int $fromWarehouseId, int $toWarehouseId): void
    {
        if ($fromWarehouseId <= 0 || $toWarehouseId <= 0) {
            throw new \RuntimeException('Hiányzó raktár az átvezetéshez.');
        }

        if ($fromWarehouseId === $toWarehouseId) {
            throw new \RuntimeException('A forrás és cél raktár nem lehet azonos.');
        }

        $items = WarehouseTransferItem::query()->where('warehouse_transfer_id', $transfer->id)->get();
        if ($items->isEmpty()) {
            return;
        }

        $productIds = $items->pluck('product_id')->filter()->unique()->values()->all();
        if (count($productIds) === 0) {
            return;
        }

        $fromStocks = DB::table('product_stocks')
            ->where('warehouse_id', '=', $fromWarehouseId)
            ->whereIn('product_id', $productIds)
            ->lockForUpdate()
            ->get(['product_id', 'quantity']);

        $toStocks = DB::table('product_stocks')
            ->where('warehouse_id', '=', $toWarehouseId)
            ->whereIn('product_id', $productIds)
            ->lockForUpdate()
            ->get(['product_id', 'quantity']);

        $fromByProduct = $fromStocks->keyBy('product_id');
        $toByProduct = $toStocks->keyBy('product_id');

        foreach ($items as $item) {
            if (!$item->product_id) {
                continue;
            }

            $move = (float) ($item->quantity ?? 0);
            if ($move <= 0) {
                continue;
            }

            $fromCurrent = (float) (($fromByProduct[$item->product_id]->quantity ?? 0) ?? 0);
            $toCurrent = (float) (($toByProduct[$item->product_id]->quantity ?? 0) ?? 0);

            $fromNew = $fromCurrent - $move;
            if ($fromNew < 0) {
                throw new \RuntimeException('Nincs elegendő készlet a forrás raktárban: ' . $item->name);
            }

            $toNew = $toCurrent + $move;

            DB::table('product_stocks')->updateOrInsert(
                [
                    'warehouse_id' => $fromWarehouseId,
                    'product_id' => (int) $item->product_id,
                ],
                [
                    'quantity' => $fromNew,
                    'updated_at' => now(),
                ]
            );

            DB::table('product_stocks')->updateOrInsert(
                [
                    'warehouse_id' => $toWarehouseId,
                    'product_id' => (int) $item->product_id,
                ],
                [
                    'quantity' => $toNew,
                    'updated_at' => now(),
                ]
            );
        }

        $transfer->update([
            'stock_moved_at' => now(),
        ]);
    }

    private function generateAndStorePdf(WarehouseTransfer $transfer, array $itemsForPdf): string
    {
        $user = auth('admin')->user();
        $createdBy = $user ? (string) ($user->name ?? $user->email ?? '') : '';

        $fromWarehouseId = (int) ($transfer->from_warehouse_id ?? 0);
        $toWarehouseId = (int) ($transfer->to_warehouse_id ?? 0);

        $fromWarehouse = Warehouse::query()->find($fromWarehouseId);
        $toWarehouse = Warehouse::query()->find($toWarehouseId);

        if (!$fromWarehouse || !$toWarehouse) {
            throw new \RuntimeException('Kérlek válassz raktárakat.');
        }

        $pdf = Pdf::loadView('pdf.warehouse-transfer', [
            'warehouse_transfer' => $transfer,
            'items' => $itemsForPdf,
            'fromWarehouse' => $fromWarehouse,
            'toWarehouse' => $toWarehouse,
            'createdBy' => $createdBy,
        ]);

        $bytes = $pdf->output();

        $month = now()->format('Y-m');
        $dir = 'private/warehouse-transfers/' . $month;
        $fileName = 'raktarkozi-atvezetes-' . $transfer->id . '.pdf';
        $relativePath = $dir . '/' . $fileName;

        $oldPath = (string) ($transfer->pdf_path ?? '');
        if ($oldPath !== '' && $oldPath !== $relativePath && Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        Storage::disk('local')->put($relativePath, $bytes);

        return $relativePath;
    }
}
