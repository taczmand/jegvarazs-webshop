<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DownloadRequest;
use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class DownloadsController extends Controller
{
    public function index()
    {
        return view('admin.settings.downloads');
    }

    public function data()
    {
        $downloads = Download::select(['id', 'file_name', 'file_path', 'file_description', 'status', 'created_at as created', 'updated_at as updated']);

        return DataTables::of($downloads)
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
            ->addColumn('action', function ($download) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-download')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $download->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-download')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $download->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(DownloadRequest $request)
    {
        try {

            $originalName = pathinfo($request->file_upload->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $request->file_upload->getClientOriginalExtension();
            $random = substr(Str::random(6), 0, 6); // 6 karakteres random string
            $filename = $originalName . '_' . $random . '.' . $extension;

            $path = $request->file_upload->storeAs('downloads', $filename, 'public');

            $download = Download::create([
                'file_name' => $request->file_name,
                'file_path' => $path,
                'file_description' => $request['file_description'] ?? null,
                'status' => $request['status'] ?? 'inactive'
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'product' => $download,
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Letöltés mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $download = Download::findOrFail($request->id);

            $download->update([
                'file_name' => $request['file_name'],
                'file_description' => $request['file_description'],
                'status' => $request['status'] ?? 'inactive'
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'download' => $download,
            ], 200);
        } catch (\Exception $e) {

            \Log::error('Letöltés mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request) {

        $download = Download::findOrFail($request->id);

        try {
            Storage::disk('public')->delete($download->file_path);
            $download->delete();

            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Letöltés törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }

    }
}
