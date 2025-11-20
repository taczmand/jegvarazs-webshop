<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BlogsController extends Controller
{
    public function index()
    {
        return view('admin.blog');
    }

    public function data()
    {
        $posts = BlogPost::select([
            'blog_posts.id',
            'blog_posts.title',
            'blog_posts.featured_image',
            'blog_posts.status',
            'blog_posts.created_at as created',
            'users.name as creator_name'
        ])->leftJoin('users', 'blog_posts.user_id', '=', 'users.id');

        return DataTables::of($posts)
            ->filterColumn('id', function ($query, $keyword) {
                if (is_numeric($keyword)) {
                    $query->where('blog_posts.id', '=', $keyword);
                }
            })
            ->addColumn('status', function ($row) {
                $translations = [
                    'published' => 'Élesítve',
                    'archived' => 'Archiválva',
                    'draft' => 'Szerkesztés alatt'
                ];

                return $translations[$row->status] ?? ucfirst($row->status);
            })
            ->addColumn('creator_name', function ($blog) {
                return $blog->creator_name ?? 'Ismeretlen';
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', '=', "{$keyword}");
            })
            ->orderColumn('status', function ($query, $order) {
                $query->orderBy('status', $order);
            })
            ->addColumn('action', function ($blog) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-blog')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $blog->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-blog')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $blog->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                }

                return $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'blog_title' => 'required|string|max:255',
                'blog_content' => 'required|string',
                'image_upload' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10000',
            ]);

            $image = $request->file('image_upload');

            if ($image && $image->isValid()) {
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $image->getClientOriginalExtension();
                $random = Str::random(6);
                $filename = $originalName . '_' . $random . '.' . $extension;

                $path = $image->storeAs('blogs', $filename, 'public');

                $fullPath = Storage::disk('public')->path($path);

                try {
                    $imagick = new \Imagick($fullPath);

                    // Formátum beállítása
                    if (in_array($extension, ['jpg', 'jpeg'])) {
                        $imagick->setImageFormat('jpeg');
                        $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                        $imagick->setImageCompressionQuality(75);
                        $imagick->setInterlaceScheme(\Imagick::INTERLACE_JPEG);
                    }

                    if ($extension === 'png') {
                        $imagick->setImageFormat('png');
                        $imagick->setImageCompression(\Imagick::COMPRESSION_ZIP);
                        $imagick->setImageCompressionQuality(75); // PNG-nél nem sokat ér
                    }

                    // Metaadatok törlése
                    $imagick->stripImage();

                    // Mentés
                    $imagick->writeImage($fullPath);
                    $imagick->clear();
                    $imagick->destroy();
                } catch (\Exception $e) {
                    \Log::error("Kép optimalizálás sikertelen: {$fullPath} - {$e->getMessage()}");
                }
            } else {
                $path = null;
            }

            $blogpost = BlogPost::create([
                'title' => $request->input('blog_title'),
                'slug' => Str::slug($request->input('blog_title')),
                'content' => $request->input('blog_content'),
                'featured_image' => $path,
                'status' => $request->input('status') ?? 'draft',
                'user_id' => auth('admin')->id()
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'blogpost' => $blogpost,
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Blog bejegyzés mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetch(Request $request)
    {
        $blog = BlogPost::findOrFail($request->id);

        return response()->json([
            'id' => $blog->id,
            'title' => $blog->title,
            'content' => $blog->content,
            'featured_image' => $blog->featured_image,
            'status' => $blog->status,
        ]);
    }

    public function deleteBlogPhoto(Request $request)
    {
        $blog = BlogPost::findOrFail($request->id);

        if ($blog->featured_image && \Storage::disk('public')->exists($blog->featured_image)) {
            \Storage::disk('public')->delete($blog->featured_image);
        }

        $blog->featured_image = null;
        $blog->save();

        return response()->json([
            'message' => 'Kép sikeresen törölve!',
            'blogpost' => $blog,
        ]);
    }

    public function update(Request $request)
    {
        try {
            $blogpost = BlogPost::findOrFail($request->id);

            $blogpost->update([
                'title' => $request['blog_title'],
                'slug' => Str::slug($request['blog_title']),
                'content' => $request['blog_content'],
                'status' => $request['status'] ?? 'draft',
                'user_id' => auth('admin')->id()
            ]);

            $image = $request->file('image_upload');

            if ($image && $image->isValid()) {
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $image->getClientOriginalExtension();
                $random = Str::random(6);
                $filename = $originalName . '_' . $random . '.' . $extension;

                $path = $image->storeAs('blogs', $filename, 'public');

                $fullPath = Storage::disk('public')->path($path);

                try {
                    $imagick = new \Imagick($fullPath);

                    // Formátum beállítása
                    if (in_array($extension, ['jpg', 'jpeg'])) {
                        $imagick->setImageFormat('jpeg');
                        $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                        $imagick->setImageCompressionQuality(75);
                        $imagick->setInterlaceScheme(\Imagick::INTERLACE_JPEG);
                    }

                    if ($extension === 'png') {
                        $imagick->setImageFormat('png');
                        $imagick->setImageCompression(\Imagick::COMPRESSION_ZIP);
                        $imagick->setImageCompressionQuality(75); // PNG-nél nem sokat ér
                    }

                    // Metaadatok törlése
                    $imagick->stripImage();

                    // Mentés
                    $imagick->writeImage($fullPath);
                    $imagick->clear();
                    $imagick->destroy();
                } catch (\Exception $e) {
                    \Log::error("Kép optimalizálás sikertelen: {$fullPath} - {$e->getMessage()}");
                }

                $blogpost->update([
                    'featured_image' => $path,
                ]);
            }

            return response()->json([
                'message' => 'Sikeres mentés!',
                'blogpost' => $blogpost,
            ], 200);
        } catch (\Exception $e) {

            \Log::error('Blog bejegyzés mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id) {

        $blogpost = BlogPost::findOrFail($id);

        try {
            if ($blogpost->featured_image) {
                if (Storage::disk('public')->exists($blogpost->featured_image)) {
                    Storage::disk('public')->delete($blogpost->featured_image);
                }
            }
            $blogpost->delete();

            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Blog törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }

    }
}
