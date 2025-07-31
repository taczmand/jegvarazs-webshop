<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanySiteRequest;
use App\Models\CompanySite;
use Yajra\DataTables\Facades\DataTables;

class CompanySiteController extends Controller
{
    public function index()
    {
        return view('admin.settings.sites');
    }

    public function data()
    {
        $sites = CompanySite::select(['id', 'name', 'country', 'zip_code', 'city', 'address_line', 'phone', 'email', 'info', 'created_at as created', 'updated_at as updated']);

        return DataTables::of($sites)
            ->addColumn('action', function ($site) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-site')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $site->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-site')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $site->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action', 'info'])
            ->make(true);
    }

    public function store(CompanySiteRequest $request)
    {
        try {
            $site = CompanySite::create([
                'name' => $request->site_name,
                'country' => $request->site_country,
                'zip_code' => $request->site_zip,
                'city' => $request->site_city,
                'address_line' => $request->site_address,
                'phone' => $request->site_phone,
                'email' => $request->site_email,
                'info' => $request->info
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'site' => $site,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hiba történt: ' . $e->getMessage()], 500);
        }
    }

    public function update(CompanySiteRequest $request, $id)
    {
        try {
            $site = CompanySite::findOrFail($id);
            $site->update([
                'name' => $request->site_name,
                'country' => $request->site_country,
                'zip_code' => $request->site_zip,
                'city' => $request->site_city,
                'address_line' => $request->site_address,
                'phone' => $request->site_phone,
                'email' => $request->site_email,
                'info' => $request->info
            ]);

            return response()->json([
                'message' => 'Sikeres frissítés!',
                'site' => $site,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hiba történt: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $site = CompanySite::findOrFail($id);
            $site->delete();

            return response()->json(['message' => 'Sikeres törlés!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hiba történt: ' . $e->getMessage()], 500);
        }
    }
}
