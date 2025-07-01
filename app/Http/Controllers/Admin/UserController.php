<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            ->addColumn('roles', function ($user) {
                return $user->roles->pluck('name')->join(', ');
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

}
