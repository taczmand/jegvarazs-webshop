<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class LeadController extends Controller
{
    public function index()
    {
        return view('admin.business.leads');
    }

    public function data()
    {
        $leads = Lead::select([
            'id',
            'full_name',
            'email',
            'phone',
            'city',
            'form_name',
            'campaign_name',
            'status',
            'created_at',
            'viewed_by',
            'viewed_at',
        ]);

        return DataTables::of($leads)
            ->editColumn('created_at', function ($item) {
                return $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '';
            })
            ->addColumn('viewed_by', function ($item) {
                if ($item->viewed_by) {
                    $tooltip = $item->viewed_at
                        ? \Carbon\Carbon::parse($item->viewed_at)->format('Y-m-d H:i:s')
                        : '';
                    return '<span title="' . e($tooltip) . '">' . e($item->viewed_by) . '</span>';
                }
                return '<span class="text-warning"><i class="fa-solid fa-eye-slash"></i></span>';
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', '=', "{$keyword}");
            })
            ->addColumn('action', function ($item) {
                $user = auth('admin')->user();
                $actions = '';

                if ($user && $user->can('edit-lead')) {
                    $actions .= '
                    <button class="btn btn-sm btn-primary edit" data-id="' . $item->id . '" title="Szerkesztés">
                        <i class="fas fa-edit"></i>
                    </button>';
                }

                if ($user && $user->can('delete-lead')) {
                    $actions .= '
                <button class="btn btn-sm btn-danger delete" data-id="' . $item->id . '" title="Törlés">
                    <i class="fas fa-trash"></i>
                </button>';
                }

                if ($user && $user->can('edit-lead')) {
                    if ($item->viewed_at) {
                        $actions .= '
                        <button class="btn btn-sm btn-warning reset-viewed" data-id="' . $item->id . '" title="Látta visszavonása">
                            <i class="fas fa-eye-slash"></i>
                        </button>';
                    }
                }

                return $actions;
            })
            ->setRowClass(function ($item) {
                return $item->viewed_by ? '' : 'fw-bold'; // ha nincs viewed_by → vastag
            })
            ->rawColumns(['action', 'viewed_by'])
            ->make(true);
    }

    public function show($id)
    {
        $lead = Lead::findOrFail($id);
        return response()->json($lead);
    }

    public function update(Request $request)
    {
        try {
            $lead = Lead::findOrFail($request['id']);

            $lead->update([
                'status' => $request['lead_status'],
                'comment' => $request['lead_comment']
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'lead' => $lead,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Érdeklődő mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request) {

        try {
            $lead = Lead::findOrFail($request->id);
            $lead->delete();

            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Kategória törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }

    }

    public function resetViewed(Request $request) {
        try {
            $lead = Lead::findOrFail($request->id);
            $lead->update([
                'viewed_at' => null,
                'viewed_by' => null,
            ]);
            return response()->json([
                'message' => 'Sikeres visszavonás!',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Érdeklődő visszavonási hiba: ' . $e->getMessage());
            return response()->json([
                'message' => 'Hiba történt a visszavonás során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
