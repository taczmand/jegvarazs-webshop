<?php

namespace App\Exceptions;

use Exception;

class OutOfStockException extends Exception
{
    public function render($request)
    {
        return redirect()->back()->withErrors(['cart' => $this->getMessage()]);
    }
}
