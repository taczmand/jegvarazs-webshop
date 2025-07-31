<?php
namespace App\Services\Order\PaymentHandlers;

use App\Models\Order;
use App\Models\OrderItem;

interface PaymentHandlerInterface
{
    /**
     * A fizetési mód kezeléséhez szükséges műveletek (pl. redirect)
     */
    public function handleRedirect(Order $order, $order_items);
}
