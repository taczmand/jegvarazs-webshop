<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InvoiceServiceInterface;
use App\Services\SzamlazzHu\Dto\CustomerData;
use App\Services\SzamlazzHu\Dto\InvoiceData;
use App\Services\SzamlazzHu\Dto\ItemData;
use Illuminate\Http\Request;

class SzamlazzHuTestController extends Controller
{
    public function createTestInvoice(Request $request, InvoiceServiceInterface $invoiceService)
    {
        $user = auth('admin')->user();
        if (!$user) {
            abort(403);
        }

        if (!$request->boolean('confirm')) {
            return response()->json([
                'ok' => false,
                'message' => 'Teszt számla létrehozásához add meg a confirm=1 paramétert.',
            ], 422);
        }

        // Intentionally fixed dummy invoice; do NOT use real customer data here.
        $invoiceData = new InvoiceData(
            customer: new CustomerData(
                name: 'Teszt Vásárló',
                zip: '1111',
                city: 'Budapest',
                address: 'Teszt utca 1.',
                taxNumber: null,
                email: null,
            ),
            items: [
                new ItemData(
                    name: 'Teszt termék',
                    quantity: 1,
                    unitPrice: 1000.0,
                    vatPercent: 27,
                    unit: 'db',
                ),
            ],
            paymentMethod: 'bank_transfer',
            currency: 'HUF',
        );

        try {
            $invoiceNumber = $invoiceService->createInvoice($invoiceData);

            return response()->json([
                'ok' => true,
                'message' => 'Teszt számla sikeresen létrehozva.',
                'invoice_number' => $invoiceNumber,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ], 502);
        }
    }
}
