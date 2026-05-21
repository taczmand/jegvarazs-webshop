<?php

namespace App\Http\Controllers;

use App\Mail\PartnerOfferMail;
use App\Models\PartnerOffer;
use App\Models\PartnerOfferItem;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PartnerOfferController extends Controller
{
    public function index()
    {
        $customer = auth('customer')->user();
        abort_unless($customer && $customer->is_partner, 403);

        $offers = PartnerOffer::query()
            ->where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->get();

        return view('pages.partner_offers.index', compact('offers'));
    }

    public function create()
    {
        $customer = auth('customer')->user();
        abort_unless($customer && $customer->is_partner, 403);

        return view('pages.partner_offers.create');
    }

    public function products(Request $request)
    {
        $customer = auth('customer')->user();
        abort_unless($customer && $customer->is_partner, 403);

        $q = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->select(['id', 'title', 'gross_price'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%");
            })
            ->orderBy('title')
            ->limit(30)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'title' => $p->title,
                    'gross_price' => (float) $p->gross_price,
                ];
            })
            ->values();

        return response()->json(['products' => $products]);
    }

    public function previewPdf(Request $request)
    {
        $customer = auth('customer')->user();
        abort_unless($customer && $customer->is_partner, 403);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'note' => 'nullable|string|max:20000',
            'items_json' => 'required|string|max:200000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Hibás adatok.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $items = json_decode((string) $request->input('items_json'), true);
            if (!is_array($items) || count($items) === 0) {
                return response()->json([
                    'message' => 'Legalább 1 tétel kötelező.',
                ], 422);
            }

            $offer = new PartnerOffer([
                'customer_id' => $customer->id,
                'title' => (string) $request->input('title'),
                'recipient_email' => null,
                'note' => $request->input('note') ?: null,
                'sent_at' => null,
                'pdf_path' => null,
            ]);

            $productIds = collect($items)
                ->filter(fn ($i) => ($i['type'] ?? null) === 'product')
                ->pluck('product_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $titlesById = $productIds->count()
                ? Product::query()->whereIn('id', $productIds)->pluck('title', 'id')->toArray()
                : [];

            $pdfItems = [];
            foreach ($items as $i) {
                $type = (string) ($i['type'] ?? 'custom');
                $qty = (int) ($i['quantity'] ?? 1);
                $qty = $qty > 0 ? $qty : 1;
                $grossPrice = (float) ($i['gross_price'] ?? 0);

                if ($type === 'product') {
                    $productId = (int) ($i['product_id'] ?? 0);
                    if (!$productId) {
                        continue;
                    }

                    $title = (string) ($titlesById[$productId] ?? 'N/A');
                    $pdfItems[] = [
                        'title' => $title,
                        'quantity' => $qty,
                        'gross_price' => $grossPrice,
                    ];
                    continue;
                }

                $title = trim((string) ($i['title'] ?? ''));
                if ($title === '') {
                    continue;
                }

                $pdfItems[] = [
                    'title' => $title,
                    'quantity' => $qty,
                    'gross_price' => $grossPrice,
                ];
            }

            if (count($pdfItems) === 0) {
                return response()->json([
                    'message' => 'Legalább 1 érvényes tétel kötelező.',
                ], 422);
            }

            $pdf = Pdf::loadView('pdf.partner_offer', [
                'offer' => $offer,
                'items' => $pdfItems,
            ]);

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="partner_ajanlat_elonezet.pdf"',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Partner offer PDF preview failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Hiba történt a PDF előnézet generálása során.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $customer = auth('customer')->user();
        abort_unless($customer && $customer->is_partner, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'recipient_email' => 'required|email|max:255',
            'note' => 'nullable|string|max:20000',
            'items_json' => 'required|string|max:200000',
        ]);

        $items = json_decode($validated['items_json'], true);
        if (!is_array($items) || count($items) === 0) {
            return back()->withErrors(['items_json' => 'Legalább 1 tétel kötelező.'])->withInput();
        }

        DB::beginTransaction();

        try {
            $offer = PartnerOffer::create([
                'customer_id' => $customer->id,
                'title' => $validated['title'],
                'recipient_email' => $validated['recipient_email'],
                'note' => $validated['note'] ?? null,
                'sent_at' => null,
                'pdf_path' => null,
            ]);

            $productIds = collect($items)
                ->filter(fn ($i) => ($i['type'] ?? null) === 'product')
                ->pluck('product_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $titlesById = $productIds->count()
                ? Product::query()->whereIn('id', $productIds)->pluck('title', 'id')->toArray()
                : [];

            $pdfItems = [];

            foreach ($items as $i) {
                $type = (string) ($i['type'] ?? 'custom');
                $qty = (int) ($i['quantity'] ?? 1);
                $qty = $qty > 0 ? $qty : 1;
                $grossPrice = (float) ($i['gross_price'] ?? 0);

                if ($type === 'product') {
                    $productId = (int) ($i['product_id'] ?? 0);
                    if (!$productId) {
                        continue;
                    }

                    $title = (string) ($titlesById[$productId] ?? 'N/A');

                    PartnerOfferItem::create([
                        'partner_offer_id' => $offer->id,
                        'type' => 'product',
                        'product_id' => $productId,
                        'title' => $title,
                        'quantity' => $qty,
                        'gross_price' => $grossPrice,
                    ]);

                    $pdfItems[] = [
                        'title' => $title,
                        'quantity' => $qty,
                        'gross_price' => $grossPrice,
                    ];

                    continue;
                }

                $title = trim((string) ($i['title'] ?? ''));
                if ($title === '') {
                    continue;
                }

                PartnerOfferItem::create([
                    'partner_offer_id' => $offer->id,
                    'type' => 'custom',
                    'product_id' => null,
                    'title' => $title,
                    'quantity' => $qty,
                    'gross_price' => $grossPrice,
                ]);

                $pdfItems[] = [
                    'title' => $title,
                    'quantity' => $qty,
                    'gross_price' => $grossPrice,
                ];
            }

            if (count($pdfItems) === 0) {
                DB::rollBack();
                return back()->withErrors(['items_json' => 'Legalább 1 érvényes tétel kötelező.'])->withInput();
            }

            $pdf = Pdf::loadView('pdf.partner_offer', [
                'offer' => $offer,
                'items' => $pdfItems,
            ]);

            $fileName = 'partner_offer_' . $offer->id . '.pdf';
            $path = 'partner_offers/' . $fileName;
            Storage::put($path, $pdf->output());

            $offer->update(['pdf_path' => $path]);

            Mail::to($offer->recipient_email)->send(new PartnerOfferMail($offer));
            $offer->update(['sent_at' => now()]);

            DB::commit();

            return redirect()->route('partner.offers.index')->with('success', 'Ajánlat elküldve.');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Partner offer store failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['message' => 'Hiba történt az ajánlat elkészítésekor.'])->withInput();
        }
    }

    public function resend(Request $request, $id)
    {
        $customer = auth('customer')->user();
        abort_unless($customer && $customer->is_partner, 403);

        $offer = PartnerOffer::query()
            ->where('customer_id', $customer->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'recipient_email' => 'required|email|max:255',
        ]);

        $offer->update(['recipient_email' => $validated['recipient_email']]);

        Mail::to($offer->recipient_email)->send(new PartnerOfferMail($offer));
        $offer->update(['sent_at' => now()]);

        return back()->with('success', 'Ajánlat elküldve.');
    }
}
