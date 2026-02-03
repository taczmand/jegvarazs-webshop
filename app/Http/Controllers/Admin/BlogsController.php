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
                'cta_title' => 'nullable|string|max:255',
                'cta_url' => 'nullable|string|max:255',
                'image_upload' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10000',
            ]);

            $image = $request->file('image_upload');

            if ($image && $image->isValid()) {

                // ---- FÁJLNÉV TISZTÍTÁSA ----
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);

                // Ékezetek eltávolítása
                $cleanName = Str::ascii($originalName);

                // Kisbetűsítés
                $cleanName = strtolower($cleanName);

                // Szóközök -> kötőjel
                $cleanName = str_replace(' ', '-', $cleanName);

                // Minden nem megengedett karakter eltávolítása
                $cleanName = preg_replace('/[^a-z0-9\-]/', '', $cleanName);

                // Többszörös kötőjelek összevonása
                $cleanName = preg_replace('/-+/', '-', $cleanName);

                // Biztonság kedvéért, ha valamiért üres lenne
                if (empty($cleanName)) {
                    $cleanName = 'image';
                }

                $extension = strtolower($image->getClientOriginalExtension());
                $random = Str::random(6);

                // A végeredmény MINDIG WebP
                $finalFilename = $cleanName . '_' . $random . '.webp';
                $finalPath = 'blogs/' . $finalFilename;
                $finalFullPath = Storage::disk('public')->path($finalPath);

                // ---- IDEIGLENES MENTÉS EREDETI FORMÁTBAN ----
                $tempFilename = $cleanName . '_' . $random . '_orig.' . $extension;
                $tempPath = $image->storeAs('blogs', $tempFilename, 'public');
                $tempFullPath = Storage::disk('public')->path($tempPath);

                try {
                    $imagick = new \Imagick($tempFullPath);

                    // ---- WEBP KONVERZIÓ ----
                    $imagick->setImageFormat('webp');
                    $imagick->setImageCompressionQuality(75); // jó kompromisszum 1MB alatt

                    // Metaadatok törlése
                    $imagick->stripImage();

                    // WebP mentése
                    $imagick->writeImage($finalFullPath);

                    $imagick->clear();
                    $imagick->destroy();

                    // ---- IDEIGLENES FÁJL TÖRLÉSE ----
                    Storage::disk('public')->delete($tempPath);

                    $path = $finalPath; // a végeredmény WebP

                } catch (\Exception $e) {
                    \Log::error("Kép optimalizálás sikertelen: {$finalFullPath} - {$e->getMessage()}");
                    $path = null;
                }

            } else {
                $path = null;
            }


            $blogpost = BlogPost::create([
                'title' => $request->input('blog_title'),
                'slug' => Str::slug($request->input('blog_title')),
                'content' => $request->input('blog_content'),
                'featured_image' => $path,
                'cta_title' => trim((string) $request->input('cta_title')) !== '' ? $request->input('cta_title') : 'Érdeklődés',
                'cta_url' => trim((string) $request->input('cta_url')) !== '' ? $request->input('cta_url') : url('/ajanlatkeres'),
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
            'cta_title' => $blog->cta_title,
            'cta_url' => $blog->cta_url,
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

            $request->validate([
                'blog_title' => 'required|string|max:255',
                'blog_content' => 'required|string',
                'cta_title' => 'nullable|string|max:255',
                'cta_url' => 'nullable|string|max:255',
                'image_upload' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10000',
            ]);

            $blogpost->update([
                'title' => $request['blog_title'],
                'slug' => Str::slug($request['blog_title']),
                'content' => $request['blog_content'],
                'cta_title' => trim((string) $request->input('cta_title')) !== '' ? $request->input('cta_title') : 'Érdeklődés',
                'cta_url' => trim((string) $request->input('cta_url')) !== '' ? $request->input('cta_url') : url('/ajanlatkeres'),
                'status' => $request['status'] ?? 'draft',
                'user_id' => auth('admin')->id()
            ]);

            $image = $request->file('image_upload');

            if ($image && $image->isValid()) {

                // Eredeti név + tisztítás
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);

                // Ékezetek eltávolítása
                $cleanName = Str::ascii($originalName);

                // Kisbetűsre állítás
                $cleanName = strtolower($cleanName);

                // Szóközök -> kötőjel
                $cleanName = str_replace(' ', '-', $cleanName);

                // Minden nem megengedett karakter eltávolítása
                $cleanName = preg_replace('/[^a-z0-9\-]/', '', $cleanName);

                // Többszörös kötőjelek -> 1 kötőjel
                $cleanName = preg_replace('/-+/', '-', $cleanName);

                // Ha üres lenne:
                if (empty($cleanName)) {
                    $cleanName = 'image';
                }

                $random = Str::random(6);

                // Végső webp fájl neve
                $filename = $cleanName . '_' . $random . '.webp';

                // Ideiglenes mentés eredeti formátumban
                $tempPath = $image->storeAs('blogs', $cleanName . '_' . $random . '_orig.' . $image->getClientOriginalExtension(), 'public');
                $tempFullPath = Storage::disk('public')->path($tempPath);

                // WebP végső mentési útvonal
                $finalPath = 'blogs/' . $filename;
                $finalFullPath = Storage::disk('public')->path($finalPath);

                try {
                    $imagick = new \Imagick($tempFullPath);

                    // WebP-re konvertálás
                    $imagick->setImageFormat('webp');
                    $imagick->setImageCompressionQuality(75);

                    // Metaadatok törlése
                    $imagick->stripImage();

                    // Mentés webp formátumban
                    $imagick->writeImage($finalFullPath);

                    $imagick->clear();
                    $imagick->destroy();

                    // Ideiglenes fájl törlése
                    Storage::disk('public')->delete($tempPath);

                } catch (\Exception $e) {
                    \Log::error("WebP konverzió sikertelen: {$finalFullPath} - {$e->getMessage()}");
                }

                // WebP útvonal mentése az adatbázisba
                $blogpost->update([
                    'featured_image' => $finalPath,
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
