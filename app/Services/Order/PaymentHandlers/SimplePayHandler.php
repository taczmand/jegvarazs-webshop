<?php
namespace App\Services\Order\PaymentHandlers;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class SimplePayHandler implements PaymentHandlerInterface
{
    public function handleRedirect(Order $order, $cart_items)
    {
        $order_total = 0;
        foreach ($cart_items as $item) {
            $order_total += $item['gross_price'] * $item['quantity'];
        }

        $endpoint = env('SIMPLEPAY_SANDBOX', true)
            ? 'https://sandbox.simplepay.hu/payment/v2/start'
            : 'https://secure.simplepay.hu/payment/v2/start';

        $data = [
            'merchant' => env('SIMPLEPAY_MERCHANT'),
            'orderRef' => (string) $order->id,
            'total' => (string)$order_total,
            'currency' => env('SIMPLEPAY_CURRENCY', 'HUF'),
            'sdkVersion' => "SimplePayV2.1_Payment_PHP_SDK_2.0.7_190701:dd236896400d7463677a82a47f53e36e",
            'language' => env('SIMPLEPAY_LANG', 'HU'),
            'twoStep' => false,
            'url' => env('SIMPLEPAY_RETURN_URL'),
            'methods' => ['CARD'],
            'customerEmail' => $order->contact_email,
            'timeout'       => now()->addMinutes(15)->format('c'),
            "invoice" => [
                "name"=>$order->billing_name,
                "company"=>"",
                "country"=>$order->billing_country,
                "state"=> $order->billing_city,
                "city"=> $order->billing_city,
                "zip"=> $order->billing_postal_code,
                "address"=> $order->billing_address_line,
                "phone"=> $order->contact_phone,
            ]
        ];

        $jsonPayload = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $signature = base64_encode(
            hash_hmac(
                'sha384', // ezt a hash-t várja a v2
                $jsonPayload,
                trim(env('SIMPLEPAY_SECRET_KEY')),
                true
            )
        );

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Signature'    => $signature,
        ])->withBody($jsonPayload, 'application/json')
            ->post($endpoint);

        if ($response->successful() && isset($response['paymentUrl'])) {
            return redirect($response['paymentUrl']);
        }

        throw new \Exception('SimplePay payment start failed: ' . $response->body());
    }
}
