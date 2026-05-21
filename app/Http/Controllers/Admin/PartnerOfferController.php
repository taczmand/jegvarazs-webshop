<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnerOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartnerOfferController extends Controller
{
    public function index()
    {
        return view('admin.business.partner_offers');
    }

    public function data(Request $request)
    {
        $offers = PartnerOffer::query()
            ->select([
                'partner_offers.id',
                'partner_offers.customer_id',
                'partner_offers.title',
                'partner_offers.recipient_email',
                'partner_offers.sent_at',
                'partner_offers.created_at',
                'customers.first_name',
                'customers.last_name',
                'customers.email as customer_email',
            ])
            ->leftJoin('customers', 'customers.id', '=', 'partner_offers.customer_id');

        return \Yajra\DataTables\Facades\DataTables::of($offers)
            ->addColumn('customer', function ($row) {
                $name = trim(($row->last_name ?? '') . ' ' . ($row->first_name ?? ''));
                $email = (string) ($row->customer_email ?? '');
                return trim($name . ($email ? ' (' . $email . ')' : ''));
            })
            ->editColumn('sent_at', function ($row) {
                return $row->sent_at ? \Carbon\Carbon::parse($row->sent_at)->format('Y-m-d H:i:s') : '';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : '';
            })
            ->addColumn('action', function ($row) {
                $pdfUrl = e(route('admin.partner-offers.pdf', ['id' => $row->id]));
                return '
                    <a class="btn btn-sm btn-outline-secondary" href="' . $pdfUrl . '" target="_blank">PDF</a>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($id)
    {
        $offer = PartnerOffer::with(['customer', 'items'])->findOrFail($id);

        return view('admin.business.partner_offer_show', compact('offer'));
    }

    public function pdf($id)
    {
        $offer = PartnerOffer::findOrFail($id);
        abort_unless($offer->pdf_path && Storage::exists($offer->pdf_path), 404);

        return Storage::download($offer->pdf_path, 'partner_ajanlat_' . $offer->id . '.pdf');
    }

    public function pdfInline($id)
    {
        $offer = PartnerOffer::findOrFail($id);
        abort_unless($offer->pdf_path && Storage::exists($offer->pdf_path), 404);

        $path = Storage::path($offer->pdf_path);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="partner_ajanlat_' . $offer->id . '.pdf"',
        ]);
    }
}
