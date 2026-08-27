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
        
        return response()->json($documents);
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
            'file' => 'required|file|max:102400', // 100MB max
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $fileType = $file->getClientMimeType();
        $fileSize = $file->getSize();

        // Store file in documents folder with organized structure by year/month
        $yearMonth = now()->format('Y-m');
        $filePath = $file->storeAs("documents/{$yearMonth}", Str::random(40) . '.' . $file->getClientOriginalExtension(), 'public');

        $document = Document::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
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
            $filePath = $file->storeAs("documents/{$yearMonth}", Str::random(40) . '.' . $file->getClientOriginalExtension(), 'public');

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
