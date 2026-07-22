<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySite;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Product;
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

        $companySites = CompanySite::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'country',
                'zip_code',
                'city',
                'address_line',
                'email',
                'phone',
            ]);

        return view('admin.documents.delivery-notes', [
            'companies' => $companies,
            'defaultCompanyId' => $defaultCompanyId,
            'companySites' => $companySites,
        ]);
    }

    public function data()
    {
        $notes = DeliveryNote::query()->select([
            'id',
            'company_id',
            'document_number',
            'partner_name',
            'issued_at',
            'delivered_at',
            'status',
            'pdf_path',
            'created_at as created',
            'updated_at as updated',
        ]);

        return DataTables::of($notes)
            ->addColumn('action', function ($note) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-delivery-note')) {
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
            'company_site_id' => 'required|integer|exists:company_sites,id',

            'partnerable_type' => 'nullable|string|max:255',
            'partnerable_id' => 'nullable|integer',

            'document_number' => 'nullable|string|max:255|unique:delivery_notes,document_number',
            'status' => 'nullable|string|max:50',

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

            'note_before_items' => 'nullable|string',
            'note_after_items' => 'nullable|string',
            'note' => 'nullable|string',

            'items_json' => 'nullable|string',
        ]);

        $company = Company::query()->where('status', 'active')->find($validated['company_id']);
        if (!$company) {
            return response()->json(['message' => 'Kérlek válassz egy céget.'], 422);
        }

        $companySite = CompanySite::query()->find($validated['company_site_id']);
        if (!$companySite) {
            return response()->json(['message' => 'Kérlek válassz egy telephelyet.'], 422);
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

        $companySiteId = (int) $validated['company_site_id'];
        unset($validated['company_site_id']);

        $payload = array_merge([
            'status' => 'draft',
        ], $validated);

        $note = DB::transaction(function () use ($payload, $request, $companySiteId) {
            $number = trim((string) ($payload['document_number'] ?? ''));
            $payload['document_number'] = $number !== '' ? $number : 'DRAFT-' . uniqid();

            $note = DeliveryNote::create($payload);

            if (str_starts_with((string) $note->document_number, 'DRAFT-')) {
                $note->update([
                    'document_number' => 'DRAFT-' . $note->id,
                ]);
            }

            $this->syncItemsFromJson($note->id, (string) $request->input('items_json', '[]'));

            $itemsForPdf = $this->parseItemsForPdf((string) $request->input('items_json', '[]'));
            $relativePath = $this->generateAndStorePdf($note->fresh(), $companySiteId, $itemsForPdf);
            $note->update([
                'pdf_path' => $relativePath,
            ]);

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
            'company_site_id' => 'required|integer|exists:company_sites,id',

            'partnerable_type' => 'nullable|string|max:255',
            'partnerable_id' => 'nullable|integer',

            'document_number' => 'nullable|string|max:255|unique:delivery_notes,document_number,' . $note->id,
            'status' => 'nullable|string|max:50',

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

            'note_before_items' => 'nullable|string',
            'note_after_items' => 'nullable|string',
            'note' => 'nullable|string',

            'items_json' => 'nullable|string',
        ]);

        $company = Company::query()->where('status', 'active')->find($validated['company_id']);
        if (!$company) {
            return response()->json(['message' => 'Kérlek válassz egy céget.'], 422);
        }

        $companySite = CompanySite::query()->find($validated['company_site_id']);
        if (!$companySite) {
            return response()->json(['message' => 'Kérlek válassz egy telephelyet.'], 422);
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

        $companySiteId = (int) $validated['company_site_id'];

        unset($validated['company_site_id']);

        $note = DB::transaction(function () use ($note, $validated, $request, $companySiteId) {
            $number = trim((string) ($validated['document_number'] ?? ''));
            if ($number === '') {
                unset($validated['document_number']);
            }

            $note->update($validated);
            $this->syncItemsFromJson($note->id, (string) $request->input('items_json', '[]'));

            $itemsForPdf = $this->parseItemsForPdf((string) $request->input('items_json', '[]'));
            $relativePath = $this->generateAndStorePdf($note->fresh(), $companySiteId, $itemsForPdf);
            $note->update([
                'pdf_path' => $relativePath,
            ]);

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

        $absolute = storage_path('app/' . ltrim($path, '/'));
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
            'company_site_id' => 'required|integer|exists:company_sites,id',
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
            'note_before_items' => 'nullable|string',
            'note_after_items' => 'nullable|string',
            'note' => 'nullable|string',
            'items_json' => 'required|string',
        ]);

        $items = $this->parseItemsForPdf((string) $validated['items_json']);
        if (count($items) === 0) {
            return response()->json(['message' => 'Nincs tétel a szállítólevélen.'], 422);
        }

        $company = Company::query()->where('status', 'active')->find($validated['company_id']);
        if (!$company) {
            return response()->json(['message' => 'Kérlek válassz egy céget.'], 422);
        }

        $companySite = CompanySite::query()->find($validated['company_site_id']);
        if (!$companySite) {
            return response()->json(['message' => 'Kérlek válassz egy telephelyet.'], 422);
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

        $note = new DeliveryNote($noteData);

        $pdf = Pdf::loadView('pdf.delivery-note', [
            'delivery_note' => $note,
            'items' => $items,
            'company_site' => $companySite,
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
            'company_site_id' => 'required|integer|exists:company_sites,id',
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
            'note_before_items' => 'nullable|string',
            'note_after_items' => 'nullable|string',
            'note' => 'nullable|string',
            'items_json' => 'required|string',
        ]);

        $itemsForPdf = $this->parseItemsForPdf((string) $validated['items_json']);
        if (count($itemsForPdf) === 0) {
            return response()->json(['message' => 'Nincs tétel a szállítólevélen.'], 422);
        }

        $companySiteId = (int) $validated['company_site_id'];

        $pdfBytes = DB::transaction(function () use ($note, $validated, $itemsForPdf, $companySiteId) {
            $note->refresh();

            $companySite = CompanySite::query()->find($companySiteId);
            if (!$companySite) {
                throw new \RuntimeException('Kérlek válassz egy telephelyet.');
            }

            $validatedForUpdate = $validated;
            unset($validatedForUpdate['company_site_id']);

            $company = Company::query()->where('status', 'active')->find($validatedForUpdate['company_id'] ?? null);
            if (!$company) {
                throw new \RuntimeException('Kérlek válassz egy céget.');
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

            $note->update($validatedForUpdate);
            $this->syncItemsFromJson($note->id, (string) $validated['items_json']);

            $pdf = Pdf::loadView('pdf.delivery-note', [
                'delivery_note' => $note->fresh(),
                'items' => $itemsForPdf,
                'company_site' => $companySite,
            ]);

            $bytes = $pdf->output();

            $month = now()->format('Y-m');
            $dir = 'private/delivery-notes/' . $month;
            $fileName = 'szallitolevel-' . $note->id . '.pdf';
            $relativePath = $dir . '/' . $fileName;

            Storage::disk('local')->put($relativePath, $bytes);

            if ($note->stock_deducted_at === null) {
                $this->deductStockForIssuedDeliveryNote($note->fresh(), $companySiteId);
            }

            $note->update([
                'pdf_path' => $relativePath,
                'status' => 'issued',
                'stock_deducted_at' => $note->stock_deducted_at ?: now(),
            ]);

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

        if ($note->stock_deducted_at !== null) {
            return response()->json([
                'message' => 'A kiállított (készletet csökkentő) szállítólevél nem törölhető.',
            ], 422);
        }

        DB::transaction(function () use ($note) {
            $path = (string) ($note->pdf_path ?? '');
            if ($path !== '' && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
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

            DeliveryNoteItem::create([
                'delivery_note_id' => $deliveryNoteId,
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

    private function deductStockForIssuedDeliveryNote(DeliveryNote $note, int $companySiteId): void
    {
        if ($companySiteId <= 0) {
            throw new \RuntimeException('Hiányzó telephely a készlet csökkentéshez.');
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
            ->where('company_site_id', '=', $companySiteId)
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
                    'company_site_id' => $companySiteId,
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

    private function generateAndStorePdf(DeliveryNote $note, int $companySiteId, array $itemsForPdf): string
    {
        if ($companySiteId <= 0) {
            throw new \RuntimeException('Hiányzó telephely a PDF generáláshoz.');
        }

        $companySite = CompanySite::query()->find($companySiteId);
        if (!$companySite) {
            throw new \RuntimeException('Kérlek válassz egy telephelyet.');
        }

        $pdf = Pdf::loadView('pdf.delivery-note', [
            'delivery_note' => $note,
            'items' => $itemsForPdf,
            'company_site' => $companySite,
        ]);

        $bytes = $pdf->output();

        $month = now()->format('Y-m');
        $dir = 'private/delivery-notes/' . $month;
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
