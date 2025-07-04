<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.settings.users');
    }

    public function data()
    {
        $users = User::with('roles')
            ->where('id', '!=', auth('admin')->id()) // kizárjuk a belépett usert
            ->select(['id', 'name', 'email', 'created_at']);

        return DataTables::of($users)
            ->editColumn('created_at', function ($user) {
                return $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : '';
            })
            ->addColumn('action', function ($user) {
                return '
                    <button class="btn btn-sm btn-primary edit" data-id="'.$user->id.'" title="Szerkesztés">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete" data-id="'.$user->id.'" title="Törlés">
                        <i class="fas fa-trash"></i>
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getRoles()
    {
        $roles = Role::select(['id', 'name'])->get();

        return response()->json($roles);
    }

    public function getPermissions()
    {
        $permissions = Permission::select(['id', 'name', 'label', 'group'])
            ->orderBy('group')
            ->orderBy('label')
            ->get()
            ->groupBy('group')
            ->map(function ($grouped) {
                return $grouped->values(); // hogy ne legyenek kulcsok
            });

        return response()->json($permissions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password'))
            ]);

            // Permission kezelése
            if ($request->has('permissions')) {
                $permissions = $request->input('permissions');
                $user->syncPermissions($permissions);
            }


            return response()->json([
                'message' => 'Sikeres mentés!',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Felhasználó mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            $user = User::findOrFail($id);
            $user->name = $request->input('name');
            $user->email = $request->input('email');

            if ($request->filled('password')) {
                $user->password = bcrypt($request->input('password'));
            }

            $user->save();

            // Permission kezelése
            if ($request->has('permissions')) {
                $permissions = $request->input('permissions');
                $user->syncPermissions($permissions);
            }

            return response()->json([
                'message' => 'Sikeres frissítés!',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Felhasználó frissítési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a frissítés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json(['message' => 'Felhasználó sikeresen törölve!'], 200);
        } catch (\Exception $e) {
            \Log::error('Felhasználó törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchWithPermissions($id)
    {
        $user = User::with('permissions')
            ->where('id', $id)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Felhasználó nem található'], 404);
        }

        return response()->json($user);
    }
}
