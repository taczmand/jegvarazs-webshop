<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $customer = auth('customer')->user();

        if (!$customer) {
            return redirect('/bejelentkezes')->with('error', 'Be kell jelentkezned a kosár megtekintéséhez.');
        }

        // A kosarat az elemeivel együtt töltjük be
        $cart = $customer->cart()->with('items.product')->first();

        return view('pages.cart',compact('cart'));
    }
    public function add(CartRequest $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {

            $customer = $request->user('customer');

            $cart = Cart::firstOrCreate([
                'customer_id' => $customer->id,
            ]);

            $item = $cart->items()->where('product_id', $request->product_id)->first();

            if ($item) {
                $item->quantity += $request->quantity;
                $item->save();
            } else {
                $cart->items()->create([
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                ]);
            }
            $cart->touch();
        } catch (\Exception $e) {
            return response()->json(['result' => 'error', 'error_message' => $e->getMessage()], 200);
        }

        return response()->json(['result' => 'success', 'cart_count' => $cart->items()->count()], 200);
    }

    function fetchSummary(Request $request)
    {
        $customer = $request->user('customer');

        $cart = Cart::where('customer_id', $customer->id)->with('items.product')->first();

        if (!$cart) {
            return response()->json(['result' => 'error', 'error_message' => 'Cart not found'], 404);
        }

        $summary = [
            'total_items' => $cart->items->sum('quantity'),
            //'total_items' => $cart->items->count(),
            'total_price' => $cart->items->sum(function ($item) {
                return $item->product->gross_price * $item->quantity;
            }),
            'items' => $cart->items,
        ];

        return response()->json(['result' => 'success', 'summary' => $summary], 200);
    }

    public function removeItemFromCart(Request $request)
    {
        $customer = $request->user('customer');

        if (!$customer) {
            return response()->json(['result' => 'error', 'error_message' => 'Be kell jelentkezned a kosár módosításához.'], 401);
        }

        $cart = $customer->cart;

        if (!$cart) {
            return response()->json(['result' => 'error', 'error_message' => 'Kosár nem található.'], 404);
        }

        $item = $cart->items()->find($request->item_id);

        if (!$item) {
            return response()->json(['result' => 'error', 'error_message' => 'Kosár elem nem található.'], 404);
        }

        $cart->touch();

        $item->delete();

        return response()->json(['result' => 'success', 'message' => 'Elem eltávolítva a kosárból.'], 200);
    }

    public function changeItemQty(Request $request)
    {
        try {
            $request->validate([
                'item_id' => 'required|exists:cart_items,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $customer = $request->user('customer');

            if (!$customer) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'Be kell jelentkezned a kosár módosításához.'
                ], 401);
            }

            $cart = $customer->cart;

            if (!$cart) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'Kosár nem található.'
                ], 404);
            }

            $item = $cart->items()->find($request->item_id);

            if (!$item) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'Kosár elem nem található.'
                ], 404);
            }

            $item->quantity = $request->quantity;
            $cart->touch();
            $item->save(); // Observer itt lefut

            return response()->json([
                'result' => 'success',
                'message' => 'Elem mennyisége frissítve.',
                'new_quantity' => $item->quantity,
            ], 200);

        } catch (\App\Exceptions\OutOfStockException $e) {
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage()
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'result' => 'error',
                'message' => 'Érvénytelen adatok.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'result' => 'error',
                'message' => 'Váratlan hiba történt.'
            ], 500);
        }
    }
}
