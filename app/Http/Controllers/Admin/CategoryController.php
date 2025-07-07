<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.products.categories');
    }

    public function data()
    {
        $categories = Category::select([
            'categories.id as id',
            'categories.title as title',
            'categories.description as description',
            'parent.title as parent_title', // szülőkategória neve alias néven
            'categories.status as status',
            'categories.created_at as created',
            'categories.updated_at as updated'
        ])
            ->leftJoin('categories as parent', 'categories.parent_id', '=', 'parent.id');

        return DataTables::of($categories)
            ->addColumn('status', function ($row) {
                $translations = [
                    'active' => 'Aktív',
                    'inactive' => 'Inaktív'
                ];

                return $translations[$row->status] ?? ucfirst($row->status);
            })
            ->addColumn('parent', function ($category) {
                return $category->parent_title ?? ''; // ha nincs szülő, üres string
            })
            ->orderColumn('status', function ($query, $order) {
                $query->orderBy('categories.status', $order);
            })
            ->filterColumn('id', function ($query, $keyword) {
                if (is_numeric($keyword)) {
                    $query->where('categories.id', $keyword);
                }
            })
            ->filterColumn('title', function ($query, $keyword) {
                $query->where('categories.title', 'like', "%{$keyword}%");
            })
            ->filterColumn('description', function ($query, $keyword) {
                $query->where('categories.description', 'like', "%{$keyword}%");
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('categories.status', '=', "{$keyword}");
            })

            ->orderColumn('parent_title', function ($query, $order) {
                $query->orderBy('categories.title', $order);
            })
            ->addColumn('action', function ($category) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-category')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $category->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-category')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $category->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function fetch() {
        $categories = Category::active()->orderBy('title')->get();
        return response()->json($categories);
    }

    public function store(CategoryRequest $request)
    {
        try {
            $category = Category::create([
                'title' => $request['cat_title'],
                'slug' => Str::slug($request['cat_title']),
                'description' => $request['cat_description'] ?? null,
                'parent_id' => $request['cat_parent_id'] ?? null,
                'status' => $request['status'] ?? 'inactive'
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'category' => $category,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Kategória mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(CategoryRequest $request)
    {
        try {
            $category = Category::findOrFail($request['id']);

            $category->update([
                'title' => $request['cat_title'],
                'slug' => Str::slug($request['cat_title']),
                'description' => $request['cat_description'] ?? null,
                'parent_id' => $request['cat_parent_id'] ?? null,
                'status' => $request['status'] ?? 'inactive'
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'category' => $category,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Kategória mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request) {

        try {
            $category = Category::findOrFail($request->id);
            $category->delete();

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
}
