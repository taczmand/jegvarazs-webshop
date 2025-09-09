<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BasicMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BasicMediaController extends Controller
{
    public function index()
    {
        return view('admin.settings.media');
    }

    public function data()
    {
        $media = BasicMedia::select(['id', 'key', 'file_path', 'comment', 'created_at as created', 'updated_at as updated']);

        return DataTables::of($media)
            ->addColumn('action', function ($media) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-media-settings')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $media->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function update(Request $request)
    {
        try {
            $media = BasicMedia::findOrFail($request['id']);

            $media->update([
                'comment' => $request['comment']
            ]);

            if ($request->hasFile('file_upload')) {

                $originalName = pathinfo($request->file_upload->getClientOriginalName(), PATHINFO_FILENAME);
                $originalName = str_replace(' ', '_', $originalName); // szóköz helyett "_"

                $extension = $request->file_upload->getClientOriginalExtension();
                $random = substr(Str::random(6), 0, 6); // 6 karakteres random string

                $filename = $originalName . '_' . $random . '.' . $extension;

                $path = $request->file_upload->storeAs('media', $filename, 'public');

                // Ha van új fájl, akkor frissítjük a fájlt
                if ($media->file_path) {
                    // Töröljük a régi fájlt, ha létezik
                    \Storage::disk('public')->delete($media->file_path);
                }
                $media->file_path = $path;
                $media->save();
            }

            return response()->json([
                'message' => 'Sikeres mentés!',
                '$media' => $media,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Média mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
