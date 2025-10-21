<?php

namespace App\Http\Controllers;

use App\Mail\NewOrder;
use App\Mail\UpdateOrder;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class SimplePayController extends Controller
{
    public function callback(Request $request)
    {
        try {
            $rawContent = $request->getContent();
            $signature = $request->header('Signature');

            $expectedSignature = base64_encode(
                hash_hmac(
                    'sha384',
                    $rawContent,
                    env('SIMPLEPAY_SECRET_KEY'),
                    true
                )
            );

            if (!hash_equals($expectedSignature, $signature)) {
                \Log::warning('SimplePay signature mismatch', [
                    'expected' => $expectedSignature,
                    'received' => $signature
                ]);
                return response('Invalid signature', 400);
            }

            $payload = json_decode($rawContent, true);

            $orderRef = $payload['orderRef'] ?? null;
            $status = $payload['status'] ?? null;

            if (!$orderRef) {
                return response()->json(['status' => 'error', 'message' => 'Missing orderRef'], 400);
            }

            $order = Order::find($orderRef);
            if (!$order) {
                return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
            }

            switch ($status) {
                case 'FINISHED':
                    $order->status = 'paid';
                    break;
                case 'FAILED':
                case 'NOTAUTHORIZED':
                    $order->status = 'payment_failed';
                    break;
                case 'TIMEOUT':
                    $order->status = 'timeout';
                    break;
                case 'CANCELED':
                    $order->status = 'cancelled';
                    break;
                case 'PENDING':
                    $order->status = 'pending';
                    break;
                default:
                    $order->status = 'unknown';
                    break;
            }

            $order->save();

            $order_items = $order->items;
            Mail::to($order->contact_email)->send(new UpdateOrder($order, $order_items));

            // --- 🔹 IPN válasz összeállítása a dokumentáció szerint ---
            $payload['receiveDate'] = now()->format('Y-m-d\TH:i:sP'); // pl. 2025-10-21T13:22:45+0200
            $responseBody = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $responseSignature = base64_encode(
                hash_hmac(
                    'sha384',
                    $responseBody,
                    env('SIMPLEPAY_SECRET_KEY'),
                    true
                )
            );

            \Log::info('SimplePay IPN response', [
                'response' => $responseBody,
                'signature' => $responseSignature
            ]);

            return response($responseBody, 200)
                ->header('Content-Type', 'application/json')
                ->header('Signature', $responseSignature);

        } catch (\Exception $e) {
            \Log::error('SimplePay callback error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    public function return (Request $request)
    {
        \Log::info('SimplePay return', $request->all());

        //$customer = auth('customer')->user();
        //$cart = $customer->cart;

        $encoded = $request->input('r');
        $json = base64_decode($encoded);
        $data = json_decode($json, true);

        $order_id = $data['o'];
        $transaction_id = $data['t'];
        $status = $data['e'];

        if (!$order_id) {
            return redirect()->route('checkout')->with('error', 'Hibás fizetési visszatérés.');
        }

        $order = Order::find($order_id);

        if (!$order) {
            return redirect()->route('checkout')->with('error', 'Nincs megkezdett rendelése.');
        }

        $order_total = $order->items->sum(function ($item) {
            return $item->gross_price * $item->quantity;
        });

        if ($status === 'SUCCESS') {
            // Sikeres fizetés
            $order_items = $order->items;
            Mail::to($order->contact_email)->send(new NewOrder(
                $order,
                $order_items
            ));

            return view('simplepay.success', compact('order', 'order_total'));

        } elseif ($status === 'FAIL') {
            // Sikertelen fizetés
            $order->status = 'payment_failed';
            $order->save();

            $order_items = $order->items;
            Mail::to($order->contact_email)->send(new UpdateOrder(
                $order,
                $order_items
            ));

            return view('simplepay.failed', compact('order', 'transaction_id', 'status'));

        } elseif ($status === 'CANCEL') {
            // Felhasználó megszakította
            $order->status = 'cancelled';
            $order->save();

            return view('simplepay.cancelled', compact('order', 'transaction_id', 'status'));

        } elseif ($status === 'TIMEOUT') {
            // Fizetés időtúllépés
            $order->status = 'timeout';
            $order->save();

            return view('simplepay.timeout', compact('order', 'transaction_id', 'status'));

        } else {
            // Egyéb, ismeretlen státusz
            $order->status = 'payment_failed';
            $order->save();

            return view('simplepay.failed', compact('order', 'transaction_id', 'status'));
        }

    }

    public function adattovabbitasi_nyilatkozat()
    {
        return view('simplepay.adattovabbitasi_nyilatkozat');
    }
}
