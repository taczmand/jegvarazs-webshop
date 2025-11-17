<?php
namespace App\Services\Order\PaymentHandlers;

use App\Mail\NewOrder;
use App\Mail\NewOrderToOffice;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class CodHandler implements PaymentHandlerInterface
{
    public function handleRedirect(Order $order, $order_items)
    {
        Mail::to($order->contact_email)->send(new NewOrder(
            $order,
            $order_items
        ));

        Mail::to("jegvarazsiroda@gmail.com")->send(new NewOrderToOffice(
            $order,
            $order_items
        ));

        // Utánvét esetén is vissza lehet irányítani a siker oldalra
        return redirect()->route('order.success', ['order' => $order->id])
            ->with('message', 'Köszönjük a rendelését, a megrendelt termék(ek) ellenértékét átvételkor tudja kiegyenlíteni.');
    }
}
