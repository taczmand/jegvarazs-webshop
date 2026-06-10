<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UpdateOrder;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\PartnerProduct;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function index()
    {
        return view('admin.sales.orders');
    }

    public function store(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-order')) {
            return response()->json(['message' => 'Nincs jogosultságod rendelést létrehozni.'], 403);
        }

        $data = $request->validate([
            'customer_id' => 'nullable|integer|exists:customers,id',
            'show_partner_prices' => 'nullable|boolean',
            'payment_method' => 'required|string|max:50',
            'comment' => 'nullable|string|max:2000',
            'order_date' => 'nullable|date',

            'contact_first_name' => 'nullable|string|max:255',
            'contact_last_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:255',

            'billing_name' => 'nullable|string|max:255',
            'billing_country' => 'nullable|string|max:10',
            'billing_postal_code' => 'nullable|string|max:30',
            'billing_city' => 'nullable|string|max:255',
            'billing_address_line' => 'nullable|string|max:255',
            'billing_tax_number' => 'nullable|string|max:64',

            'shipping_name' => 'nullable|string|max:255',
            'shipping_country' => 'nullable|string|max:10',
            'shipping_postal_code' => 'nullable|string|max:30',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_address_line' => 'nullable|string|max:255',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:9999',
        ]);

        /** @var Customer|null $customer */
        $customer = null;
        $billing = null;
        $shipping = null;

        if (!empty($data['customer_id'])) {
            $customer = Customer::query()
                ->with([
                    'billingAddresses' => fn ($q) => $q->latest('id'),
                    'shippingAddresses' => fn ($q) => $q->latest('id'),
                ])
                ->findOrFail((int) $data['customer_id']);

            $billing = $customer->billingAddresses->first();
            $shipping = $customer->shippingAddresses->first();
        }

        $items = collect($data['items'])
            ->map(fn ($i) => [
                'product_id' => (int) ($i['product_id'] ?? 0),
                'quantity' => (int) ($i['quantity'] ?? 0),
            ])
            ->filter(fn ($i) => $i['product_id'] > 0 && $i['quantity'] > 0)
            ->values();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Nincs érvényes termék a rendelésben.'], 422);
        }

        $products = Product::query()
            ->whereIn('id', $items->pluck('product_id')->all())
            ->with('taxCategory')
            ->get()
            ->keyBy('id');

        $usePartnerPricing = ($customer && $customer->is_partner)
            || (!$customer && $request->boolean('show_partner_prices'));

        $partnerDiscountPrices = collect();
        if ($customer && $customer->is_partner) {
            $partnerDiscountPrices = PartnerProduct::query()
                ->where('customer_id', $customer->id)
                ->whereIn('product_id', $products->keys()->all())
                ->pluck('discount_gross_price', 'product_id');
        }

        return DB::transaction(function () use ($data, $customer, $billing, $shipping, $items, $products, $partnerDiscountPrices, $usePartnerPricing) {
            if (!$customer) {
                $requiredFields = [
                    'contact_first_name',
                    'contact_last_name',
                    'contact_email',
                    'contact_phone',
                    'billing_name',
                    'billing_country',
                    'billing_postal_code',
                    'billing_city',
                    'billing_address_line',
                    'shipping_name',
                    'shipping_country',
                    'shipping_postal_code',
                    'shipping_city',
                    'shipping_address_line',
                ];

                $missing = [];
                foreach ($requiredFields as $field) {
                    if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                        $missing[] = $field;
                    }
                }

                if (!empty($missing)) {
                    return response()->json([
                        'message' => 'Vásárló nélkül a kapcsolattartó / számlázási / szállítási adatok megadása kötelező.',
                        'missing' => $missing,
                    ], 422);
                }
            }

            $orderDate = !empty($data['order_date'])
                ? Carbon::parse($data['order_date'])
                : null;

            $order = Order::create([
                'customer_id' => $customer?->id,

                'contact_first_name' => $data['contact_first_name'] ?? ($customer->first_name ?? null),
                'contact_last_name' => $data['contact_last_name'] ?? ($customer->last_name ?? null),
                'contact_email' => $data['contact_email'] ?? ($customer->email ?? null),
                'contact_phone' => $data['contact_phone'] ?? ($customer->phone ?? null),

                'billing_name' => $data['billing_name'] ?? ($billing?->name ?? trim(($customer->last_name ?? '') . ' ' . ($customer->first_name ?? ''))),
                'billing_country' => $data['billing_country'] ?? ($billing?->country ?? 'HU'),
                'billing_postal_code' => $data['billing_postal_code'] ?? ($billing?->zip_code ?? null),
                'billing_city' => $data['billing_city'] ?? ($billing?->city ?? null),
                'billing_address_line' => $data['billing_address_line'] ?? ($billing?->address_line ?? null),
                'billing_tax_number' => $data['billing_tax_number'] ?? ($billing?->tax_number ?? null),

                'shipping_name' => $data['shipping_name'] ?? ($shipping?->name ?? trim(($customer->last_name ?? '') . ' ' . ($customer->first_name ?? ''))),
                'shipping_country' => $data['shipping_country'] ?? ($shipping?->country ?? 'HU'),
                'shipping_postal_code' => $data['shipping_postal_code'] ?? ($shipping?->zip_code ?? null),
                'shipping_city' => $data['shipping_city'] ?? ($shipping?->city ?? null),
                'shipping_address_line' => $data['shipping_address_line'] ?? ($shipping?->address_line ?? null),

                'payment_method' => $data['payment_method'],
                'comment' => $data['comment'] ?? null,
                'status' => 'pending',
                'created_at' => $orderDate,
            ]);

            $storedItems = [];
            foreach ($items as $row) {
                $product = $products->get($row['product_id']);
                if (!$product) {
                    continue;
                }

                $grossPrice = (float) ($product->gross_price ?? 0);
                if ($usePartnerPricing) {
                    if ($customer && $customer->is_partner) {
                        $discount = $partnerDiscountPrices->get($product->id);
                        if ($discount !== null) {
                            $grossPrice = (float) $discount;
                        } elseif ($product->partner_gross_price !== null) {
                            $grossPrice = (float) $product->partner_gross_price;
                        }
                    } elseif ($product->partner_gross_price !== null) {
                        $grossPrice = (float) $product->partner_gross_price;
                    }
                }

                $taxValue = $product->taxCategory?->tax_value;
                if ($taxValue === null && isset($product->tax_value)) {
                    $taxValue = $product->tax_value;
                }

                $item = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->title,
                    'quantity' => (int) $row['quantity'],
                    'gross_price' => $grossPrice,
                    'tax_value' => $taxValue,
                ]);
                $storedItems[] = $item->toArray();
            }

            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => auth('admin')->id(),
                'action' => 'order_created_admin',
                'data' => json_encode([
                    'order' => $order->toArray(),
                    'items' => $storedItems,
                ]),
            ]);

            return response()->json([
                'message' => 'Rendelés sikeresen létrehozva.',
                'order' => $order->load(['items', 'customer']),
            ], 201);
        });
    }
    public function data()
    {
        $orders = Order::select([
            'orders.id',
            'orders.status',
            'orders.created_at',
            'orders.customer_id',
            'orders.contact_last_name',
            'orders.contact_first_name',
            'customers.last_name',
            'customers.first_name',
            'orders.viewed_by',
            'orders.viewed_at'
        ])->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->with('items', 'customer');

        return datatables()
            ->of($orders)
            ->orderColumn('status', function ($query, $order) {
                $query->orderBy('orders.status', $order);
            })
            ->orderColumn('customer_name', function ($query, $order) {
                $query->orderBy('customers.last_name', $order)
                    ->orderBy('customers.first_name', $order);
            })
            ->filterColumn('id', function ($query, $keyword) {
                if (is_numeric($keyword)) {
                    $query->where('orders.id', '=', $keyword);
                }
            })
            ->filterColumn('customer_name', function ($query, $keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('customers.last_name', 'like', "%{$keyword}%")
                        ->orWhere('customers.first_name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('status', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->where('orders.status', 'like', "%{$keyword}%");
                }
            })
            ->addColumn('status', function ($order) {
                $translations = [
                    'paid'   => 'Fizetve',
                    'pending'   => 'Függőben',
                    'completed' => 'Teljesítve',
                    'cancelled' => 'Törölve',
                    'processing'=> 'Feldolgozás alatt',
                    'refunded'  => 'Visszatérítve',
                    'failed'    => 'Sikertelen',
                    'payment_failed' => 'Fizetés sikertelen',
                    'timeout' => 'Időtúllépés',
                ];
                return $translations[$order->status] ?? ucfirst($order->status);
            })
            ->addColumn('total_amount', function ($order) {
                return $order->items->sum(function ($item) {
                    $grossPrice = $item->gross_price ?? 0;
                    return $item->quantity * $grossPrice;
                });
            })
            ->addColumn('items_count', function ($order) {
                return $order->items->count();
            })
            ->addColumn('customer_name', function ($order) {
                if ($order->last_name && $order->first_name) {
                    return $order->last_name . ' ' . $order->first_name;
                }

                if ($order->contact_last_name && $order->contact_first_name) {
                    return $order->contact_last_name . ' ' . $order->contact_first_name;
                }

                if ($order->customer) {
                    return $order->customer->last_name . ' ' . $order->customer->first_name;
                }

                return 'N/A';
            })
            ->addColumn('is_partner', function ($order) {
                return $order->customer && $order->customer->is_partner ? 'Igen' : 'Nem';
            })
            ->editColumn('created_at', function ($order) {
                return $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '';
            })
            ->addColumn('viewed_by', function ($order) {
                if ($order->viewed_by) {
                    return '<span title="Megtekintve: '
                        . ($order->viewed_at ? \Carbon\Carbon::parse($order->viewed_at)->format('Y-m-d H:i:s') : '-')
                        . '">' . e($order->viewed_by) . '</span>';
                }
                return '<span class="text-warning"><i class="fa-solid fa-eye-slash"></i></span>';
            })
            ->addColumn('action', function ($order) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user->can('edit-order')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $order->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user->can('delete-order')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $order->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->setRowClass(fn($order) => $order->viewed_by ? '' : 'fw-bold')
            ->rawColumns(['action', 'viewed_by'])
            ->make(true);
    }



    public function show($id)
    {
        return Order::with(['items.product', 'customer'])->findOrFail($id);
    }

    public function history($id)
    {
        return OrderHistory::with(['customer', 'user'])
            ->where('order_id', $id)
            ->get();

    }

    public function items($id)
    {
        return OrderItem::with(['product.unit', 'product.photos'])
            ->where('order_id', $id)
            ->get();
    }

    public function update($id, Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-order')) {
            return response()->json(['message' => 'Nincs jogosultságod rendelést szerkeszteni.'], 403);
        }

        $data = $request->validate([
            'status' => 'nullable|string|max:50',
            'order_comment' => 'nullable|string|max:2000',
            'contact_first_name' => 'nullable|string|max:255',
            'contact_last_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'billing_name' => 'nullable|string|max:255',
            'billing_country' => 'nullable|string|max:10',
            'billing_postal_code' => 'nullable|string|max:30',
            'billing_city' => 'nullable|string|max:255',
            'billing_address_line' => 'nullable|string|max:255',
            'billing_tax_number' => 'nullable|string|max:64',
            'shipping_name' => 'nullable|string|max:255',
            'shipping_country' => 'nullable|string|max:10',
            'shipping_postal_code' => 'nullable|string|max:30',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_address_line' => 'nullable|string|max:255',

            'items' => 'nullable|array',
            'items.*.id' => 'nullable|integer|exists:order_items,id',
            'items.*.product_id' => 'nullable|integer|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:0|max:9999',
            'items.*.delete' => 'nullable|boolean',
        ]);

        $order = Order::with('items')->findOrFail($id);

        return DB::transaction(function () use ($order, $data) {
            $order->status = $data['status'] ?? $order->status;
            $order->comment = $data['order_comment'] ?? $order->comment;
            $order->contact_first_name = $data['contact_first_name'] ?? $order->contact_first_name;
            $order->contact_last_name = $data['contact_last_name'] ?? $order->contact_last_name;
            $order->contact_email = $data['contact_email'] ?? $order->contact_email;
            $order->contact_phone = $data['contact_phone'] ?? $order->contact_phone;
            $order->billing_name = $data['billing_name'] ?? $order->billing_name;
            $order->billing_country = $data['billing_country'] ?? $order->billing_country;
            $order->billing_postal_code = $data['billing_postal_code'] ?? $order->billing_postal_code;
            $order->billing_city = $data['billing_city'] ?? $order->billing_city;
            $order->billing_address_line = $data['billing_address_line'] ?? $order->billing_address_line;
            $order->billing_tax_number = $data['billing_tax_number'] ?? $order->billing_tax_number;
            $order->shipping_name = $data['shipping_name'] ?? $order->shipping_name;
            $order->shipping_country = $data['shipping_country'] ?? $order->shipping_country;
            $order->shipping_postal_code = $data['shipping_postal_code'] ?? $order->shipping_postal_code;
            $order->shipping_city = $data['shipping_city'] ?? $order->shipping_city;
            $order->shipping_address_line = $data['shipping_address_line'] ?? $order->shipping_address_line;

            $orderDirty = $order->isDirty();

            $itemsChanged = false;
            $storedItems = null;

            if (array_key_exists('items', $data)) {
                $payloadItems = collect($data['items'] ?? [])->map(function ($row) {
                    return [
                        'id' => isset($row['id']) ? (int) $row['id'] : null,
                        'product_id' => isset($row['product_id']) ? (int) $row['product_id'] : null,
                        'quantity' => isset($row['quantity']) ? (int) $row['quantity'] : null,
                        'delete' => (bool) ($row['delete'] ?? false),
                    ];
                })->values();

                $existingItems = $order->items->keyBy('id');

                $newProductIds = $payloadItems
                    ->filter(fn ($i) => empty($i['id']) && !empty($i['product_id']) && empty($i['delete']) && (int) $i['quantity'] > 0)
                    ->pluck('product_id')
                    ->unique()
                    ->values();

                $products = $newProductIds->isNotEmpty()
                    ? Product::query()->whereIn('id', $newProductIds->all())->with('taxCategory')->get()->keyBy('id')
                    : collect();

                foreach ($payloadItems as $row) {
                    $rowId = $row['id'] ?? null;

                    if ($rowId) {
                        /** @var OrderItem|null $item */
                        $item = $existingItems->get($rowId);
                        if (!$item || (int) $item->order_id !== (int) $order->id) {
                            continue;
                        }

                        if (!empty($row['delete']) || ((int) ($row['quantity'] ?? 0)) <= 0) {
                            $itemsChanged = true;
                            $item->delete();
                            continue;
                        }

                        $newQty = (int) $row['quantity'];
                        if ((int) $item->quantity !== $newQty) {
                            $itemsChanged = true;
                            $item->quantity = $newQty;
                            $item->save();
                        }
                        continue;
                    }

                    if (!empty($row['delete']) || empty($row['product_id']) || ((int) ($row['quantity'] ?? 0)) <= 0) {
                        continue;
                    }

                    $product = $products->get((int) $row['product_id']);
                    if (!$product) {
                        continue;
                    }

                    $itemsChanged = true;

                    $taxValue = $product->taxCategory?->tax_value;
                    if ($taxValue === null && isset($product->tax_value)) {
                        $taxValue = $product->tax_value;
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->title,
                        'quantity' => (int) $row['quantity'],
                        'gross_price' => (float) ($product->gross_price ?? 0),
                        'tax_value' => $taxValue,
                    ]);
                }

                $storedItems = $this->items($order->id)->toArray();
            }

            if ($orderDirty) {
                $order->save();
            }

            if ($orderDirty || $itemsChanged) {
                OrderHistory::create([
                    'order_id' => $order->id,
                    'user_id' => auth('admin')->id(),
                    'action' => 'order_updated',
                    'data' => json_encode([
                        'order' => $order->toArray(),
                        'items' => $storedItems,
                    ]),
                ]);

                Mail::to($order->contact_email)->send(new UpdateOrder(
                    $order,
                    $storedItems ?? $this->items($order->id)->toArray()
                ));

                return response()->json([
                    'message' => 'Rendelés sikeresen frissítve.',
                    'order' => $order->fresh(['items', 'customer']),
                ], 200);
            }

            return response()->json([
                'message' => 'Nincs változás a rendelésben.',
            ], 200);
        });
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->items()->delete(); // Töröljük a rendelés tételeit
        $order->delete(); // Töröljük magát a rendelést

        return response()->json([
            'message' => 'Rendelés sikeresen törölve.',
        ], 200);
    }
}
