<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TagController extends Controller
{
    public function index()
    {
        return view('admin.products.tags');
    }

    public function data()
    {
        $tags = Tag::select(['id', 'name', 'created_at as created', 'updated_at as updated']);

        return DataTables::of($tags)
            ->addColumn('action', function ($tag) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-tag')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $tag->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-tag')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $tag->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(TagRequest $request)
    {
        try {
            $tag = Tag::create([
                'name' => $request['name']
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'tag' => $tag,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Címke mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(TagRequest $request)
    {
        try {
            $tag = Tag::findOrFail($request['id']);

            $tag->update([
                'name' => $request['name']
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'tag' => $tag,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Címke mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request) {

        try {
            $tag = Tag::findOrFail($request->id);
            $tag->delete();

            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Címke törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }

    }

}
