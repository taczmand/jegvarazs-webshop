<?php
namespace App\Services\Order\PaymentHandlers;

use App\Mail\NewOrder;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Mail;

class BankTransferHandler implements PaymentHandlerInterface
{
    public function handleRedirect(Order $order, $order_items)
    {
        Mail::to($order->contact_email)->send(new NewOrder(
            $order,
            $order_items
        ));

        // Banki átutalásnál nem kell redirect, csak vissza a siker oldalra, vagy rendelés visszaigazolás
        return redirect()->route('order.success', ['order' => $order->id])
            ->with('message', 'Kérjük, utalja el a vételárat a megadott bankszámlaszámra.');
    }
}
