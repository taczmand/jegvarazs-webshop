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

                $originalName = pathinfo($request->file_upload->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $request->file_upload->getClientOriginalExtension();
                $random = substr(Str::random(6), 0, 6); // 6 karakteres random string
                $filename = $originalName . '_' . $random . '.' . $extension;

                $path = $request->file_upload->storeAs('employees', $filename, 'public');

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

                $originalName = pathinfo($request->file_upload->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $request->file_upload->getClientOriginalExtension();
                $random = substr(Str::random(6), 0, 6); // 6 karakteres random string
                $filename = $originalName . '_' . $random . '.' . $extension;

                $path = $request->file_upload->storeAs('employees', $filename, 'public');


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
