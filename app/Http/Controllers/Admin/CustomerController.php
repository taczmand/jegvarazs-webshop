<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\CustomerBillingAddress;
use App\Models\CustomerShippingAddress;
use App\Models\PartnerProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index()
    {
        return view('admin.sales.customers');
    }

    public function data()
    {
        $customers = Customer::select([
            'id',
            'last_name',
            'first_name',
            'phone',
            'email',
            'is_partner',
            'status',
            'created_at as created',
            'updated_at as updated'
        ]);

        return DataTables::of($customers)
            ->addColumn('customer_name', function ($customer) {
                return $customer->last_name . " " . $customer->first_name;
            })
            ->filterColumn('id', function ($query, $keyword) {
                if (is_numeric($keyword)) {
                    $query->where('id', '=', $keyword);
                }
            })
            ->filterColumn('customer_name', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('email', function ($query, $keyword) {
                $query->where('email', 'like', "%{$keyword}%");
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', '=', "{$keyword}");
            })
            ->editColumn('created_at', function ($customer) {
                return $customer->created ? \Carbon\Carbon::parse($customer->created)->format('Y-m-d H:i:s') : '';
            })
            ->addColumn('action', function ($customer) {
                $user = auth('admin')->user();
                $actions = '';

                if ($user && $user->can('edit-customer')) {
                    $actions .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $customer->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>';
                }

                if ($user && $user->can('delete-customer')) {
                    $actions .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $customer->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>';
                }

                return $actions;
            })

            ->rawColumns(['action'])
            ->make(true);
    }


    public function show($id)
    {
        return response()->json([
            'customer' => Customer::with([
                'orders.items',
                'cart.items.product',
                'billingAddresses',
                'shippingAddresses'
            ])->findOrFail($id),
            'countries' => config('countries'),
        ]);
    }

    public function deleteCartItem($id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cartItem->delete();

        return response()->json(['success' => true, 'message' => 'Cart item deleted successfully.']);
    }

    public function showProductsToPartner(Request $request, $id)
    {
        $search = $request->query('product_search');

        $products = Product::with(['partnerProducts' => function ($query) use ($id) {
            $query->where('customer_id', $id);
        }])
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->get();

        return response()->json($products);
    }
    public function setProductPriceToPartner(Request $request)
    {
        $customer_id = $request->input('customer_id');
        $product_id = $request->input('product_id');
        $discount_gross_price = $request->input('discount_gross_price');

        $partner_product = PartnerProduct::updateOrCreate(
            ['customer_id' => $customer_id, 'product_id' => $product_id],
            ['discount_gross_price' => $discount_gross_price]
        );

        return response()->json(['success' => true, 'message' => 'Egyedi partnerár beállítása sikeres volt.', 'data' => $partner_product]);
    }

    public function destroyProductPriceToPartner(Request $request)
    {
        $customer_id = $request->input('customer_id');
        $product_id = $request->input('product_id');

        $partner_product = PartnerProduct::where('customer_id', $customer_id)
            ->where('product_id', $product_id)
            ->first();

        if ($partner_product) {
            $partner_product->delete();
            return response()->json(['success' => true, 'message' => 'Egyedi partnerár törlése sikeres volt.']);
        }

        return response()->json(['success' => false, 'message' => 'Egyedi partnerár nem található.'], 404);
    }

    public function setProductPricePercentToPartner(Request $request)
    {
        $customer_id = $request->input('customer_id');
        $percent = (float)$request->input('discount_percentage');

        $products = Product::all(['id', 'gross_price']);

        foreach ($products as $product) {

            if(100 === $percent) {
                $discounted_price = 0.00;
            } else {
                $discounted_price = round($product->gross_price * (1 - $percent / 100), 2);
            }

            PartnerProduct::updateOrInsert(
                [
                    'customer_id' => $customer_id,
                    'product_id' => $product->id,
                ],
                [
                    'discount_gross_price' => $discounted_price,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }


        return response()->json(['success' => true, 'message' => 'Egyedi partnerár százalékos beállítása sikeres volt.']);
    }

    public function destroyAllProductPriceToPartner(Request $request)
    {
        $customer_id = $request->input('customer_id');

        PartnerProduct::where('customer_id', $customer_id)->delete();

        return response()->json(['success' => true, 'message' => 'Minden egyedi partnerár törlése sikeres volt.']);
    }

    public function updateShippingAddress(Request $request)
    {
        $shipping_address_id = $request->input('id');
        $shipping_address = CustomerShippingAddress::findOrFail($shipping_address_id);

        $shippingAddress = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'country' => $request->input('country'),
            'zip_code' => $request->input('zip_code'),
            'city' => $request->input('city'),
            'address_line' => $request->input('address_line'),
        ];
        $shipping_address->update($shippingAddress);

        return response()->json(['success' => true, 'message' => 'Shipping address updated successfully.']);
    }

    public function destroyShippingAddress(Request $request)
    {
        $shipping_address_id = $request->input('id');
        $shipping_address = CustomerShippingAddress::findOrFail($shipping_address_id);
        $shipping_address->delete();

        return response()->json(['success' => true, 'message' => 'Shipping address deleted successfully.']);
    }

    public function updateBillingAddress(Request $request)
    {
        $billing_address_id = $request->input('id');
        $billing_address = CustomerBillingAddress::findOrFail($billing_address_id);

        $billingAddress = [
            'name' => $request->input('name'),
            'tax_number' => $request->input('tax_number'),
            'country' => $request->input('country'),
            'zip_code' => $request->input('zip_code'),
            'city' => $request->input('city'),
            'address_line' => $request->input('address_line'),
        ];
        $billing_address->update($billingAddress);

        return response()->json(['success' => true, 'message' => 'Billing address updated successfully.']);
    }
    public function destroyBillingAddress(Request $request)
    {
        $billing_address_id = $request->input('id');
        $billing_address = CustomerBillingAddress::findOrFail($billing_address_id);
        $billing_address->delete();

        return response()->json(['success' => true, 'message' => 'Billing address deleted successfully.']);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json(['success' => true, 'message' => 'Customer deleted successfully.']);
    }
    public function update(Request $request)
    {
        $customer = Customer::findOrFail($request->input('id'));

        if ($request->has('password') && !empty($request->input('password'))) {
            $customer->password = bcrypt($request->input('password'));
        }

        $customer->update($request->only(['first_name', 'last_name', 'email', 'phone', 'is_partner', 'status']));

        return response()->json(['success' => true, 'message' => 'Customer updated successfully.', 'customer' => $customer]);
    }
}
