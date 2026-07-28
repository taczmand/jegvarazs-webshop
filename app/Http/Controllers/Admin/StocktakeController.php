<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stocktake;
use App\Models\StocktakeItem;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class StocktakeController extends Controller
{
    public function index()
    {
        $user = auth('admin')->user();
        abort_unless($user && $user->can('view-stocktakes'), 403);

        $warehouses = Warehouse::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.warehouse.stocktakes', [
            'warehouses' => $warehouses,
        ]);
    }

    public function data()
    {
        $user = auth('admin')->user();
        abort_unless($user && $user->can('view-stocktakes'), 403);

        $rows = Stocktake::query()
            ->leftJoin('warehouses as w', 'w.id', '=', 'stocktakes.warehouse_id')
            ->select([
                'stocktakes.id',
                'stocktakes.warehouse_id',
                'stocktakes.name',
                'stocktakes.status',
                'stocktakes.started_at',
                'stocktakes.started_at_time',
                'stocktakes.closed_at',
                'stocktakes.closed_at_time',
                'stocktakes.pdf_path',
                'stocktakes.created_at as created',
                'stocktakes.updated_at as updated',
                DB::raw('COALESCE(w.name, "") as warehouse_name'),
            ]);

        return DataTables::of($rows)
            ->editColumn('started_at', function ($row) {
                if (!empty($row->started_at_time)) {
                    try {
                        return \Carbon\Carbon::parse($row->started_at_time)->format('Y-m-d H:i');
                    } catch (\Throwable $e) {
                        return (string) $row->started_at_time;
                    }
                }
                if (!empty($row->started_at)) {
                    try {
                        return \Carbon\Carbon::parse($row->started_at)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        return (string) $row->started_at;
                    }
                }
                return '';
            })
            ->editColumn('closed_at', function ($row) {
                if (!empty($row->closed_at_time)) {
                    try {
                        return \Carbon\Carbon::parse($row->closed_at_time)->format('Y-m-d H:i');
                    } catch (\Throwable $e) {
                        return (string) $row->closed_at_time;
                    }
                }
                if (!empty($row->closed_at)) {
                    try {
                        return \Carbon\Carbon::parse($row->closed_at)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        return (string) $row->closed_at;
                    }
                }
                return '';
            })
            ->addColumn('action', function ($row) {
                $user = auth('admin')->user();
                $buttons = '';

                $isOpen = (string) ($row->status ?? '') === 'open';

                if ($isOpen && $user && $user->can('edit-stocktake')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $row->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-stocktake')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $row->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                $pdfPath = (string) ($row->pdf_path ?? '');
                if ($pdfPath !== '' && $user && $user->can('view-stocktakes')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-outline-secondary pdf" data-id="' . $row->id . '" title="PDF">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function pdf(int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-stocktakes')) {
            abort(403);
        }

        $stocktake = Stocktake::query()->findOrFail($id);
        $path = (string) ($stocktake->pdf_path ?? '');
        if ($path === '' || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $absolute = storage_path('app/private/' . ltrim($path, '/'));
        if (!file_exists($absolute)) {
            abort(404);
        }

        return response()->file($absolute, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="leltar-' . $stocktake->id . '.pdf"',
        ]);
    }

    public function show(int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-stocktakes')) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $stocktake = Stocktake::query()->with(['items'])->findOrFail($id);

        return response()->json([
            'stocktake' => $stocktake,
            'items' => $stocktake->items,
        ]);
    }

    public function products(Request $request, ?int $id = null)
    {
        $user = auth('admin')->user();
        if (!$user || (!$user->can('view-stocktakes') && !$user->can('create-stocktake') && !$user->can('edit-stocktake'))) {
            return response()->json(['message' => 'Nincs jogosultságod.'], 403);
        }

        $validated = $request->validate([
            'warehouse_id' => 'required|integer|exists:warehouses,id',
        ]);

        $warehouseId = (int) $validated['warehouse_id'];

        $existingByProduct = collect();
        if ($id) {
            $existingByProduct = StocktakeItem::query()
                ->where('stocktake_id', $id)
                ->get(['product_id', 'counted_quantity'])
                ->keyBy('product_id');
        }

        $products = Product::query()
            ->with(['category', 'unit'])
            ->leftJoin('product_stocks as ps', function ($join) use ($warehouseId) {
                $join->on('ps.product_id', '=', 'products.id')
                    ->where('ps.warehouse_id', '=', $warehouseId);
            })
            ->addSelect([
                'products.id',
                'products.title',
                'products.cat_id',
                'products.unit_id',
                DB::raw('COALESCE(ps.quantity, 0) as current_stock'),
            ])
            ->orderBy('products.cat_id')
            ->orderBy('products.title')
            ->get();

        $payload = $products->map(function ($p) use ($existingByProduct) {
            $existing = $existingByProduct->get($p->id);

            return [
                'product_id' => (int) $p->id,
                'title' => (string) ($p->title ?? ''),
                'category_title' => (string) ($p->category?->title ?? 'Egyéb'),
                'unit' => (string) ($p->unit?->abbreviation ?? $p->unit?->name ?? 'db'),
                'current_stock' => (float) ($p->current_stock ?? 0),
                'counted_quantity' => $existing ? $existing->counted_quantity : null,
            ];
        })->values();

        return response()->json([
            'products' => $payload,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-stocktake')) {
            return response()->json(['message' => 'Nincs jogosultságod létrehozni.'], 403);
        }

        $validated = $this->validatePayload($request);

        $stocktake = DB::transaction(function () use ($validated, $user) {
            $payload = $validated;
            $itemsJson = (string) ($payload['items_json'] ?? '[]');
            unset($payload['items_json']);
            $payload['status'] = $payload['status'] ?? 'open';
            $payload['created_by_user_id'] = $user?->id;
            $payload['started_at'] = now()->toDateString();
            $payload['started_at_time'] = now();

            $closingNow = (($payload['status'] ?? null) === 'closed');
            if ($closingNow) {
                $payload['closed_at'] = $payload['closed_at'] ?? now()->toDateString();
                $payload['closed_at_time'] = $payload['closed_at_time'] ?? now();
                $payload['closed_by_user_id'] = $payload['closed_by_user_id'] ?? $user?->id;
            }

            $stocktake = Stocktake::create($payload);

            $this->syncItemsFromJson($stocktake->id, (int) $stocktake->warehouse_id, $itemsJson);

            if ($closingNow) {
                $this->applyStocktakeToProductStocks($stocktake->id, (int) $stocktake->warehouse_id);
                $stocktake->refresh();
                try {
                    $pdfPath = $this->generateAndStorePdf($stocktake);
                    $stocktake->update(['pdf_path' => $pdfPath]);
                    $stocktake->refresh();
                } catch (\Throwable $e) {
                    throw new \RuntimeException('A PDF generálása nem sikerült: ' . $e->getMessage(), 0, $e);
                }
            }

            return $stocktake;
        });

        $stocktake->refresh();

        return response()->json([
            'message' => 'Sikeres mentés!',
            'stocktake' => $stocktake,
        ], 200);
    }

    public function update(Request $request, int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-stocktake')) {
            return response()->json(['message' => 'Nincs jogosultságod szerkeszteni.'], 403);
        }

        $stocktake = Stocktake::query()->findOrFail($id);

        if ((string) $stocktake->status !== 'open') {
            return response()->json(['message' => 'A leltár lezárt, nem módosítható.'], 422);
        }

        $wasOpen = (string) $stocktake->status === 'open';

        $validated = $this->validatePayload($request, $stocktake->id);

        $stocktake = DB::transaction(function () use ($stocktake, $validated, $user, $wasOpen) {
            $payload = $validated;
            $itemsJson = (string) ($payload['items_json'] ?? '[]');
            unset($payload['items_json']);

            unset($payload['started_at']);
            unset($payload['started_at_time']);

            $closingNow = $wasOpen && (($payload['status'] ?? null) === 'closed');
            if (($payload['status'] ?? null) === 'closed') {
                $payload['closed_at'] = $payload['closed_at'] ?? now()->toDateString();
                $payload['closed_at_time'] = $payload['closed_at_time'] ?? now();
                $payload['closed_by_user_id'] = $stocktake->closed_by_user_id ?: $user?->id;
            }

            $stocktake->update($payload);

            $this->syncItemsFromJson($stocktake->id, (int) $stocktake->warehouse_id, $itemsJson);

            if ($closingNow) {
                $this->applyStocktakeToProductStocks($stocktake->id, (int) $stocktake->warehouse_id);
                $stocktake->refresh();
                try {
                    $pdfPath = $this->generateAndStorePdf($stocktake);
                    $stocktake->update(['pdf_path' => $pdfPath]);
                    $stocktake->refresh();
                } catch (\Throwable $e) {
                    throw new \RuntimeException('A PDF generálása nem sikerült: ' . $e->getMessage(), 0, $e);
                }
            }

            return $stocktake;
        });

        $stocktake->refresh();

        return response()->json([
            'message' => 'Sikeres frissítés!',
            'stocktake' => $stocktake,
        ], 200);
    }

    public function destroy(int $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('delete-stocktake')) {
            return response()->json(['message' => 'Nincs jogosultságod törölni.'], 403);
        }

        $stocktake = Stocktake::query()->findOrFail($id);

        DB::transaction(function () use ($stocktake) {
            $path = (string) ($stocktake->pdf_path ?? '');
            if ($path !== '' && Storage::exists($path)) {
                Storage::delete($path);
            }
            StocktakeItem::query()->where('stocktake_id', $stocktake->id)->delete();
            $stocktake->delete();
        });

        return response()->json(['message' => 'Sikeres törlés!'], 200);
    }

    private function validatePayload(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|string|max:50',
            'closed_at' => 'nullable|date',
            'note' => 'nullable|string',
            'items_json' => 'nullable|string',
        ]);
    }

    private function syncItemsFromJson(int $stocktakeId, int $warehouseId, string $itemsJson): void
    {
        $decoded = json_decode($itemsJson, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        $decoded = array_values(array_filter($decoded, fn ($row) => is_array($row)));

        $productIds = [];
        foreach ($decoded as $row) {
            $pid = $row['product_id'] ?? null;
            if ($pid !== null && $pid !== '' && is_numeric($pid)) {
                $productIds[] = (int) $pid;
            }
        }
        $productIds = array_values(array_unique($productIds));

        $admin = auth('admin')->user();
        $countedByUserId = $admin ? (int) ($admin->id ?? 0) : null;
        if ($countedByUserId === 0) {
            $countedByUserId = null;
        }

        $expectedByProduct = collect();
        if (count($productIds) > 0) {
            $expectedByProduct = DB::table('product_stocks')
                ->where('warehouse_id', '=', $warehouseId)
                ->whereIn('product_id', $productIds)
                ->lockForUpdate()
                ->get(['product_id', 'quantity'])
                ->keyBy('product_id');
        }

        StocktakeItem::query()->where('stocktake_id', $stocktakeId)->delete();

        foreach ($decoded as $row) {
            $pid = $row['product_id'] ?? null;
            if ($pid === null || $pid === '' || !is_numeric($pid)) {
                continue;
            }
            $productId = (int) $pid;

            $counted = $row['counted_quantity'] ?? null;
            if ($counted === '' || $counted === null) {
                continue;
            }
            if (!is_numeric($counted)) {
                continue;
            }

            $countedQty = (float) $counted;

            $expectedQty = (float) (($expectedByProduct[$productId]->quantity ?? 0) ?? 0);
            $diff = $countedQty - $expectedQty;

            StocktakeItem::create([
                'stocktake_id' => $stocktakeId,
                'product_id' => $productId,
                'counted_by_user_id' => $countedByUserId,
                'expected_quantity' => $expectedQty,
                'counted_quantity' => $countedQty,
                'difference_quantity' => $diff,
                'counted_at' => now(),
            ]);
        }
    }

    private function applyStocktakeToProductStocks(int $stocktakeId, int $warehouseId): void
    {
        $items = StocktakeItem::query()
            ->where('stocktake_id', $stocktakeId)
            ->whereNotNull('counted_quantity')
            ->get(['product_id', 'counted_quantity']);

        foreach ($items as $item) {
            $productId = (int) ($item->product_id ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $qty = (float) ($item->counted_quantity ?? 0);

            DB::table('product_stocks')->updateOrInsert(
                [
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                ],
                [
                    'quantity' => $qty,
                    'available_quantity' => $qty,
                ]
            );
        }
    }

    private function generateAndStorePdf(Stocktake $stocktake): string
    {
        $warehouse = Warehouse::query()->find((int) $stocktake->warehouse_id);

        $changedItems = StocktakeItem::query()
            ->where('stocktake_id', $stocktake->id)
            ->whereNotNull('counted_quantity')
            ->where('difference_quantity', '!=', 0)
            ->get(['product_id', 'expected_quantity', 'counted_quantity', 'difference_quantity']);

        $itemsByProduct = $changedItems->keyBy('product_id');
        $productIds = $changedItems->pluck('product_id')->filter()->unique()->values()->all();

        $productsById = Product::query()
            ->with(['category', 'unit'])
            ->whereIn('products.id', $productIds)
            ->get(['products.id', 'products.title', 'products.cat_id', 'products.unit_id'])
            ->keyBy('id');

        $fmt = function ($v) {
            if ($v === null) {
                return '';
            }
            $n = (float) $v;
            return rtrim(rtrim(number_format($n, 3, '.', ''), '0'), '.');
        };

        $rows = collect($productIds)->map(function ($productId) use ($productsById, $itemsByProduct, $fmt) {
            $p = $productsById->get($productId);
            $item = $itemsByProduct->get($productId);

            $expected = $item ? (float) ($item->expected_quantity ?? 0) : 0.0;
            $counted = $item ? (float) ($item->counted_quantity ?? 0) : null;
            $diff = $item ? (float) ($item->difference_quantity ?? 0) : null;

            return [
                'product_id' => (int) $productId,
                'title' => (string) ($p?->title ?? ''),
                'category_title' => (string) ($p?->category?->title ?? 'Egyéb'),
                'unit' => (string) ($p?->unit?->abbreviation ?? $p?->unit?->name ?? 'db'),
                'expected_quantity' => $fmt($expected),
                'counted_quantity' => $fmt($counted),
                'difference_quantity' => $fmt($diff),
            ];
        })->values();

        $pdf = Pdf::loadView('pdf.stocktake', [
            'stocktake' => $stocktake,
            'warehouse' => $warehouse,
            'rows' => $rows,
        ]);

        $bytes = $pdf->output();

        $dir = 'stocktakes/' . now()->format('Y-m');
        $fileName = 'leltar-' . $stocktake->id . '.pdf';
        $relativePath = $dir . '/' . $fileName;

        Storage::disk('local')->makeDirectory($dir);

        $oldPath = (string) ($stocktake->pdf_path ?? '');
        if ($oldPath !== '' && $oldPath !== $relativePath && Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        Storage::disk('local')->put($relativePath, $bytes);

        return $relativePath;
    }
}
