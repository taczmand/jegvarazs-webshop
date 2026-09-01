<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index()
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-documents')) {
            abort(403);
        }

        return view('admin.business.documents');
    }

    public function data()
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-documents')) {
            abort(403);
        }

        $documents = Document::with('createdBy')->orderBy('created_at', 'desc')->get();
        
        // Add file URL to each document
        $documents->transform(function ($document) {
            $document->file_url = Storage::disk('public')->url($document->file_path);
            return $document;
        });
        
        // Get unique folders
        $folders = Document::whereNotNull('folder')->distinct()->pluck('folder')->sort()->values();
        
        return response()->json([
            'documents' => $documents,
            'folders' => $folders,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('create-documents')) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'folder' => 'nullable|string|max:255',
            'file' => 'required|file|max:102400', // 100MB max
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $fileType = $file->getClientMimeType();
        $fileSize = $file->getSize();

        // Store file in documents folder with organized structure by year/month
        $yearMonth = now()->format('Y-m');
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::random(40) . '.' . $extension;
        $storagePath = "documents/{$yearMonth}/{$filename}";

        // Check if it's an image and compress it
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
            $file->storeAs("documents/{$yearMonth}", $filename, 'public');
            $fullPath = Storage::disk('public')->path($storagePath);

            if (file_exists($fullPath)) {
                try {
                    $imagick = new \Imagick($fullPath);
                    $beforeBytes = filesize($fullPath);

                    // Resize to max 1600px on the longer side
                    $maxSide = 1600;
                    $w = (int) $imagick->getImageWidth();
                    $h = (int) $imagick->getImageHeight();
                    if ($w > 0 && $h > 0 && ($w > $maxSide || $h > $maxSide)) {
                        $scale = $maxSide / max($w, $h);
                        $newW = max(1, (int) round($w * $scale));
                        $newH = max(1, (int) round($h * $scale));
                        $imagick->resizeImage($newW, $newH, \Imagick::FILTER_LANCZOS, 1, true);
                    }

                    // PNG transparency handling: flatten to white background for JPEG
                    if ($extension === 'png') {
                        $imagick->setImageBackgroundColor('white');
                        $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                    }

                    // JPEG settings
                    $imagick->setImageFormat('jpeg');
                    $imagick->setInterlaceScheme(\Imagick::INTERLACE_JPEG);
                    $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $imagick->setImageProperty('jpeg:sampling-factor', '4:2:0');
                    $imagick->setOption('jpeg:optimize-coding', 'true');
                    $imagick->setImageCompressionQuality(80);
                    $imagick->stripImage();

                    $filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
                    $jpegFilename = $filenameWithoutExt . '.jpeg';
                    $jpegStoragePath = "documents/{$yearMonth}/{$jpegFilename}";
                    $fullJpegPath = Storage::disk('public')->path($jpegStoragePath);

                    // Save as new JPEG file
                    $imagick->writeImage($fullJpegPath);
                    $imagick->destroy();

                    // Delete original file
                    Storage::disk('public')->delete($storagePath);

                    // Update file info
                    $filePath = $jpegStoragePath;
                    $fileType = 'image/jpeg';
                    $fileSize = filesize($fullJpegPath);
                } catch (\Exception $e) {
                    // If compression fails, keep original file
                    \Log::error('Document image compression failed', [
                        'error' => $e->getMessage(),
                        'file' => $fileName
                    ]);
                    $filePath = $storagePath;
                }
            } else {
                $filePath = $storagePath;
            }
        } else {
            // Non-image files - store as-is
            $filePath = $file->storeAs("documents/{$yearMonth}", $filename, 'public');
        }

        $document = Document::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'folder' => $request->input('folder'),
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'created_by' => $user->id,
        ]);

        return response()->json([
            'message' => 'Dokumentum sikeresen feltöltve.',
            'document' => $document,
        ], 201);
    }

    public function show($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-documents')) {
            abort(403);
        }

        $document = Document::with('createdBy')->findOrFail($id);
        return response()->json($document);
    }

    public function update(Request $request, $id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('edit-documents')) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'folder' => 'nullable|string|max:255',
            'file' => 'nullable|file|max:102400', // 100MB max
        ]);

        $document = Document::findOrFail($id);

        if ($request->hasFile('file')) {
            // Delete old file
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Upload new file
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileType = $file->getClientMimeType();
            $fileSize = $file->getSize();

            $yearMonth = now()->format('Y-m');
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = Str::random(40) . '.' . $extension;
            $storagePath = "documents/{$yearMonth}/{$filename}";

            // Check if it's an image and compress it
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
                $file->storeAs("documents/{$yearMonth}", $filename, 'public');
                $fullPath = Storage::disk('public')->path($storagePath);

                if (file_exists($fullPath)) {
                    try {
                        $imagick = new \Imagick($fullPath);

                        // Resize to max 1600px on the longer side
                        $maxSide = 1600;
                        $w = (int) $imagick->getImageWidth();
                        $h = (int) $imagick->getImageHeight();
                        if ($w > 0 && $h > 0 && ($w > $maxSide || $h > $maxSide)) {
                            $scale = $maxSide / max($w, $h);
                            $newW = max(1, (int) round($w * $scale));
                            $newH = max(1, (int) round($h * $scale));
                            $imagick->resizeImage($newW, $newH, \Imagick::FILTER_LANCZOS, 1, true);
                        }

                        // PNG transparency handling: flatten to white background for JPEG
                        if ($extension === 'png') {
                            $imagick->setImageBackgroundColor('white');
                            $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                        }

                        // JPEG settings
                        $imagick->setImageFormat('jpeg');
                        $imagick->setInterlaceScheme(\Imagick::INTERLACE_JPEG);
                        $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                        $imagick->setImageProperty('jpeg:sampling-factor', '4:2:0');
                        $imagick->setOption('jpeg:optimize-coding', 'true');
                        $imagick->setImageCompressionQuality(80);
                        $imagick->stripImage();

                        $filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
                        $jpegFilename = $filenameWithoutExt . '.jpeg';
                        $jpegStoragePath = "documents/{$yearMonth}/{$jpegFilename}";
                        $fullJpegPath = Storage::disk('public')->path($jpegStoragePath);

                        // Save as new JPEG file
                        $imagick->writeImage($fullJpegPath);
                        $imagick->destroy();

                        // Delete original file
                        Storage::disk('public')->delete($storagePath);

                        // Update file info
                        $filePath = $jpegStoragePath;
                        $fileType = 'image/jpeg';
                        $fileSize = filesize($fullJpegPath);
                    } catch (\Exception $e) {
                        // If compression fails, keep original file
                        \Log::error('Document image compression failed', [
                            'error' => $e->getMessage(),
                            'file' => $fileName
                        ]);
                        $filePath = $storagePath;
                    }
                } else {
                    $filePath = $storagePath;
                }
            } else {
                // Non-image files - store as-is
                $filePath = $file->storeAs("documents/{$yearMonth}", $filename, 'public');
            }

            $document->update([
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_type' => $fileType,
                'file_size' => $fileSize,
            ]);
        }

        $document->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'folder' => $request->input('folder'),
        ]);

        return response()->json([
            'message' => 'Dokumentum sikeresen frissítve.',
            'document' => $document,
        ]);
    }

    public function destroy($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('delete-documents')) {
            abort(403);
        }

        $document = Document::findOrFail($id);

        // Delete file from storage
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return response()->json([
            'message' => 'Dokumentum sikeresen törölve.',
        ]);
    }

    public function download($id)
    {
        $user = auth('admin')->user();
        if (!$user || !$user->can('view-documents')) {
            abort(403);
        }

        $document = Document::findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'A fájl nem található.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
}
