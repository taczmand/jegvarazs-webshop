<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('admin.settings.employees');
    }

    public function data()
    {
        $employees = Employee::select(['id', 'name', 'email', 'phone', 'position', 'profile_photo_path', 'created_at as created', 'updated_at as updated']);

        return DataTables::of($employees)
            ->addColumn('action', function ($employee) {
                $user = auth('admin')->user();
                $buttons = '';

                if ($user && $user->can('edit-employee')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $employee->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                if ($user && $user->can('delete-employee')) {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $employee->id . '" title="Törlés">
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

        $request->validate([
            'employee_name' => 'required|string|max:255',
            'employee_email' => 'required|string|max:255',
            'file_upload' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:2048', // 2MB max méret
        ]);

        try {



            $employee = Employee::create([
                'name' => $request['employee_name'],
                'email' => $request['employee_email'],
                'phone' => $request['employee_phone'],
                'position' => $request['employee_position']
            ]);

            if ($request->hasFile('file_upload')) {
                $employee->profile_photo_path = $this->storeEmployeePhoto($request->file_upload);
                $employee->save();
            }

            return response()->json([
                'message' => 'Sikeres mentés!',
                'employee' => $employee,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Munkatárs mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(Request $request)
    {
        try {
            $employee = Employee::findOrFail($request['id']);

            $employee->update([
                'name' => $request['employee_name'],
                'email' => $request['employee_email'],
                'phone' => $request['employee_phone'],
                'position' => $request['employee_position']
            ]);



            if ($request->hasFile('file_upload')) {
                $path = $this->storeEmployeePhoto($request->file_upload);

                // Ha van új fájl, akkor frissítjük a logót
                if ($employee->profile_photo_path) {
                    // Töröljük a régi logót, ha létezik
                    \Storage::disk('public')->delete($employee->profile_photo_path);
                }
                $employee->profile_photo_path = $path;
                $employee->save();
            }

            return response()->json([
                'message' => 'Sikeres mentés!',
                'employee' => $employee,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Munkatárs mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    private function storeEmployeePhoto($file): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower($file->getClientOriginalExtension());
        $random = substr(Str::random(6), 0, 6);
        $filename = $originalName . '_' . $random . '.' . $extension;

        $path = $file->storeAs('employees', $filename, 'public');
        $fullPath = Storage::disk('public')->path($path);

        // Csak JPG/PNG → WEBP, felbontás és minőség csökkentés
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            try {
                $imagick = new \Imagick($fullPath);

                // Max 1600px a hosszabb oldalon
                $maxSide = 1600;
                $w = (int) $imagick->getImageWidth();
                $h = (int) $imagick->getImageHeight();
                if ($w > 0 && $h > 0 && ($w > $maxSide || $h > $maxSide)) {
                    $scale = $maxSide / max($w, $h);
                    $newW = max(1, (int) round($w * $scale));
                    $newH = max(1, (int) round($h * $scale));
                    $imagick->resizeImage($newW, $newH, \Imagick::FILTER_LANCZOS, 1, true);
                }

                $imagick->setImageFormat('webp');
                $imagick->setImageCompressionQuality(75);
                $imagick->stripImage();

                $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $fullPath);
                $imagick->writeImage($webpPath);
                $imagick->destroy();

                unlink($fullPath);

                $path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $path);
            } catch (\Exception $e) {
                \Log::error("Munkatárs kép optimalizálás sikertelen: {$fullPath} - {$e->getMessage()}");
            }
        }

        return $path;
    }

    public function destroy(Request $request) {

        $employee = Employee::findOrFail($request->id);

        try {
            Storage::disk('public')->delete($employee->profile_photo_path);
            $employee->delete();

            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Munkatárs törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }

    }
}
