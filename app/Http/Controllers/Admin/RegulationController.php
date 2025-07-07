<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegulationRequest;
use App\Models\Regulation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class RegulationController extends Controller
{
    public function index()
    {
        return view('admin.settings.regulations');
    }

    public function data()
    {
        $regulations = Regulation::select(['id', 'file_name', 'file_path', 'file_description', 'status', 'created_at as created', 'updated_at as updated']);

        return DataTables::of($regulations)
            ->addColumn('status', function ($row) {
                $translations = [
                    'active' => 'Aktív',
                    'inactive' => 'Inaktív'
                ];

                return $translations[$row->status] ?? ucfirst($row->status);
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', '=', "{$keyword}");
            })
            ->orderColumn('status', function ($query, $order) {
                $query->orderBy('status', $order);
            })
            ->addColumn('action', function ($regulation) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-regulation')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $regulation->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-regulation')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $regulation->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(RegulationRequest $request)
    {
        try {

            $originalName = pathinfo($request->file_upload->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $request->file_upload->getClientOriginalExtension();
            $random = substr(Str::random(6), 0, 6); // 6 karakteres random string
            $filename = $originalName . '_' . $random . '.' . $extension;

            $path = $request->file_upload->storeAs('regulations', $filename, 'public');

            $regulation = Regulation::create([
                'file_name' => $request->file_name,
                'file_path' => $path,
                'file_description' => $request['file_description'] ?? null,
                'status' => $request['status'] ?? 'inactive'
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'regulation' => $regulation,
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Szabályzat mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $regulation = Regulation::findOrFail($request->id);

            $regulation->update([
                'file_name' => $request['file_name'],
                'file_description' => $request['file_description'],
                'status' => $request['status'] ?? 'inactive'
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'regulation' => $regulation,
            ], 200);
        } catch (\Exception $e) {

            \Log::error('Szabályzat mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request) {

        $regulation = Regulation::findOrFail($request->id);

        try {
            Storage::disk('public')->delete($regulation->file_path);
            $regulation->delete();

            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Szabályzat törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }

    }
}
