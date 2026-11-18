<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromotionRequest;
use App\Models\Promotion;
use App\Models\PromotionCoupon;
use App\Models\PromotionCouponUsage;
use App\Services\Admin\PromotionService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PromotionController extends Controller
{
    protected $promotion_service;

    public function __construct(PromotionService $service)
    {
        $this->promotion_service = $service;
    }

    public function index()
    {
        return view('admin.settings.promotions');
    }

    public function data()
    {
        $promotions = Promotion::select(['id', 'name', 'slug', 'description', 'starts_at', 'ends_at', 'discount_percent', 'active']);

        return DataTables::of($promotions)
            ->editColumn('starts_at', function ($promotion) {
                return $promotion->starts_at ? $promotion->starts_at->format('Y-m-d') : '';
            })
            ->editColumn('ends_at', function ($promotion) {
                return $promotion->ends_at ? $promotion->ends_at->format('Y-m-d') : '';
            })
            ->editColumn('discount_percent', function ($promotion) {
                return $promotion->discount_percent !== null ? (string) $promotion->discount_percent : '';
            })
            ->editColumn('active', function ($promotion) {
                return $promotion->active ? 'Aktív' : 'Inaktív';
            })
            ->addColumn('action', function ($promotion) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('view-promotions')) {
                    $buttons .= '
                        <a class="btn btn-sm btn-success" href="' . route('promotion.show', $promotion->slug) . '" target="_blank" title="Megtekintés">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    ';

                    $buttons .= '
                        <button class="btn btn-sm btn-info show-coupons" data-id="' . $promotion->id . '" data-name="' . e($promotion->name) . '" title="Kuponok">
                            <i class="fas fa-ticket-alt"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('edit-promotion')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $promotion->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-promotion')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $promotion->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(PromotionRequest $request)
    {
        $result = $this->promotion_service->store($request->validated());

        if ($result['success']) {
            return response()->json(['message' => 'Mentés sikeres', 'promotion' => $result['data']]);
        }

        return response()->json(['message' => 'Hiba történt', 'error' => $result['error']], 500);
    }

    public function update(PromotionRequest $request, Promotion $promotion)
    {
        $result = $this->promotion_service->update($promotion, $request->validated());

        if ($result['success']) {
            return response()->json(['message' => 'Mentés sikeres', 'promotion' => $result['data']]);
        }

        return response()->json(['message' => 'Hiba történt', 'error' => $result['error']], 500);
    }

    public function coupons(Promotion $promotion)
    {
        $coupons = PromotionCoupon::where('promotion_id', $promotion->id)
            ->with('usages')
            ->orderByDesc('id')
            ->get()
            ->map(function ($coupon) {
                $usage = $coupon->usages->first();
                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'assigned' => $usage !== null,
                    'used' => $usage?->used_at !== null,
                    'email' => $usage?->email,
                    'sent_at' => $usage?->sent_at?->format('Y-m-d H:i'),
                    'used_at' => $usage?->used_at?->format('Y-m-d H:i'),
                ];
            });

        return response()->json(['coupons' => $coupons]);
    }

    public function generateCoupons(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'prefix' => ['nullable', 'string', 'max:20'],
        ]);

        $result = $this->promotion_service->generateCoupons(
            $promotion,
            (int) $validated['quantity'],
            $validated['prefix'] ?? null
        );

        if ($result['success']) {
            return response()->json(['message' => count($result['data']) . ' kupon generálva.']);
        }

        return response()->json(['message' => 'Hiba történt', 'error' => $result['error']], 500);
    }

    public function useCoupon(Request $request, PromotionCoupon $coupon)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        if ($coupon->usages()->exists()) {
            return response()->json(['message' => 'Ez a kupon már felhasználták.'], 422);
        }

        PromotionCouponUsage::create([
            'promotion_coupon_id' => $coupon->id,
            'email' => $validated['email'],
        ]);

        return response()->json(['message' => 'Kupon felhasználva.']);
    }

    public function toggleCouponUsed(PromotionCoupon $coupon)
    {
        $usage = $coupon->usages()->first();

        if (!$usage) {
            return response()->json(['message' => 'A kupon még nincs kiosztva.'], 422);
        }

        $usage->used_at = $usage->used_at === null ? now() : null;
        $usage->save();

        return response()->json([
            'message' => $usage->used_at !== null ? 'Kupon felhasználtként jelölve.' : 'Felhasználás jelölése visszavonva.',
            'used' => $usage->used_at !== null,
        ]);
    }

    public function destroy(Promotion $promotion)
    {
        $result = $this->promotion_service->delete($promotion);

        if ($result['success']) {
            return response()->json(['message' => 'Sikeres törlés.']);
        }

        return response()->json([
            'error' => 'Nem sikerült törölni a promóciót.',
            'details' => $result['error']
        ], 500);
    }
}
