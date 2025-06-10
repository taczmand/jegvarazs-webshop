<?php
namespace App\Services\Order\PaymentHandlers;

use App\Models\Order;

class BankTransferHandler implements PaymentHandlerInterface
{
    public function handleRedirect(Order $order)
    {
        // Banki átutalásnál nem kell redirect, csak vissza a siker oldalra, vagy rendelés visszaigazolás
        return redirect()->route('order.success', ['order' => $order->id])
            ->with('message', 'Kérjük, utalja el a vételárat a megadott bankszámlaszámra.');
    }
}
