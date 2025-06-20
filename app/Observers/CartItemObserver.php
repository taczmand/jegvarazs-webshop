<?php

namespace App\Observers;

use App\Exceptions\OutOfStockException;
use App\Models\CartItem;

class CartItemObserver
{
    /**
     * Handle the CartItem "created" event.
     */
    public function created(CartItem $cartItem): void
    {
        $product = $cartItem->product;

        if ($product->stock < $cartItem->quantity) {
            throw new OutOfStockException("Nincs elég készlet a(z) {$product->name} termékből.");
        }

        $product->decrement('stock', $cartItem->quantity);
    }

    /**
     * Handle the CartItem "updated" event.
     */
    public function updated(CartItem $cartItem): void
    {
        if ($cartItem->wasChanged('quantity')) {
            $oldQty = $cartItem->getOriginal('quantity');
            $newQty = $cartItem->quantity;
            $diff = $newQty - $oldQty;

            if ($diff > 0) {
                // Növekedés esetén ellenőrizni kell, hogy van-e ennyi készlet
                if ($cartItem->product->stock < $diff) {
                    throw new OutOfStockException("Nem lehet ennyi terméket hozzáadni, nincs elég készlet.");
                }
                $cartItem->product->decrement('stock', $diff);
            } elseif ($diff < 0) {
                // Csökkenésnél visszatesszük a különbséget
                $cartItem->product->increment('stock', abs($diff));
            }
        }
    }

    /**
     * Handle the CartItem "deleted" event.
     */
    public function deleted(CartItem $cartItem): void
    {
        $cartItem->product->increment('stock', $cartItem->quantity);
    }

    /**
     * Handle the CartItem "restored" event.
     */
    public function restored(CartItem $cartItem): void
    {
        //
    }

    /**
     * Handle the CartItem "force deleted" event.
     */
    public function forceDeleted(CartItem $cartItem): void
    {
        //
    }

    public function creating(CartItem $cartItem)
    {
        $this->checkStock($cartItem);
    }

    public function updating(CartItem $cartItem)
    {
        $this->checkStock($cartItem);
    }

    protected function checkStock(CartItem $cartItem)
    {
        $product = $cartItem->product;

        if (!$product) {
            throw new \Exception("A termék nem található.");
        }

        if ($cartItem->quantity > $product->stock) {
            throw new OutOfStockException("Nincs elég készlet a(z) {$product->name} termékből.");
        }

        if ($cartItem->quantity < 0) {
            throw new \Exception("A mennyiség nem lehet negatív.");
        }
    }
}
