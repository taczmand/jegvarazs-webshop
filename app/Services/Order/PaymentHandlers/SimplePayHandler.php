<?php
namespace App\Services\Order\PaymentHandlers;

use App\Models\Order;

class SimplePayHandler implements PaymentHandlerInterface
{
    public function handleRedirect(Order $order)
    {
        // Itt jönne a SimplePay fizetés elindítása
        // Példa: előkészítesz adatokat, átirányítasz a SimplePay oldalra

        // Ez csak vázlat, a tényleges SimplePay integráció komplexebb
        return redirect()->route('simplepay.redirect', ['order' => $order->id]);
    }
}
