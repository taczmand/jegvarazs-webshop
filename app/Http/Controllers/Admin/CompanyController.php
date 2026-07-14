<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{
    public function index()
    {
        return view('admin.settings.companies');
    }

    public function data()
    {
        $companies = Company::query()->select([
            'id',
            'name',
            'tax_number',
            'vat_number',
            'country',
            'zip_code',
            'city',
            'address_line',
            'email',
            'phone',
            'bank_account',
            'status',
            'is_default',
            'created_at as created',
            'updated_at as updated',
        ]);

        return DataTables::of($companies)
            ->addColumn('action', function ($company) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-company')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $company->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-company')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $company->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->editColumn('is_default', function ($company) {
                return (bool) $company->is_default;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-company')) {
            return response()->json(['message' => 'Nincs jogosultságod létrehozni.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'address_line' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'is_default' => 'nullable|boolean',
        ]);

        $payload = array_merge([
            'country' => 'HU',
            'status' => 'active',
            'is_default' => false,
        ], $validated);

        if (!array_key_exists('country', $payload) || !$payload['country']) {
            $payload['country'] = 'HU';
        }
        if (!array_key_exists('status', $payload) || !$payload['status']) {
            $payload['status'] = 'active';
        }

        $company = Company::create($payload);

        if ((bool) ($company->is_default ?? false)) {
            Company::query()->where('id', '!=', $company->id)->update(['is_default' => false]);
        }

        return response()->json([
            'message' => 'Sikeres mentés!',
            'company' => $company,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-company')) {
            return response()->json(['message' => 'Nincs jogosultságod szerkeszteni.'], 403);
        }

        $company = Company::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'address_line' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'is_default' => 'nullable|boolean',
        ]);

        $company->update($validated);

        if ((bool) ($company->is_default ?? false)) {
            Company::query()->where('id', '!=', $company->id)->update(['is_default' => false]);
        }

        return response()->json([
            'message' => 'Sikeres frissítés!',
            'company' => $company,
        ], 200);
    }

    public function destroy($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('delete-company')) {
            return response()->json(['message' => 'Nincs jogosultságod törölni.'], 403);
        }

        $company = Company::findOrFail($id);
        $company->delete();

        return response()->json(['message' => 'Sikeres törlés!'], 200);
    }
}
