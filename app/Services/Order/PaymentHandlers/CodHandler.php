<?php
namespace App\Services\Order\PaymentHandlers;

use App\Models\Order;

class CodHandler implements PaymentHandlerInterface
{
    public function handleRedirect(Order $order)
    {
        // Utánvét esetén is vissza lehet irányítani a siker oldalra
        return redirect()->route('order.success', ['order' => $order->id])
            ->with('message', 'Készpénzes fizetés a futárnál átvételkor.');
    }
}
