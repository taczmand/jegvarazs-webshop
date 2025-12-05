<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutomatedEmail;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AutomatedEmailController extends Controller
{
    public function index()
    {
        return view('admin.business.automated');
    }

    public function data()
    {
        $leads = AutomatedEmail::select([
            'id',
            'email_address',
            'email_template',
            'frequency_interval',
            'frequency_unit',
            'last_sent_at',
            'created_at',
            'updated_at'
        ]);

        return DataTables::of($leads)
            ->editColumn('created_at', function ($item) {
                return $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '';
            })
            ->editColumn('updated_at', function ($item) {
                return $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '';
            })
            ->addColumn('action', function ($item) {
                $user = auth('admin')->user();
                $actions = '';

                if ($user && $user->can('edit-lead')) {
                    $actions .= '
                <button class="btn btn-sm btn-primary edit" data-id="' . $item->id . '" title="Szerkesztés">
                    <i class="fas fa-edit"></i>
                </button>';
                }

                if ($user && $user->can('delete-lead')) {
                    $actions .= '
                <button class="btn btn-sm btn-danger delete" data-id="' . $item->id . '" title="Törlés">
                    <i class="fas fa-trash"></i>
                </button>';
                }

                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        try {
            $automatedEmail = AutomatedEmail::create([
                'email_template' => $request['email_template'],
                'email_address' => $request['email_address'],
                'frequency_interval' => $request['frequency_interval'],
                'frequency_unit' => $request['frequency_unit'],
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'category' => $automatedEmail,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Automatizáció mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(Request $request)
    {
        try {
            $automatedEmail = AutomatedEmail::findOrFail($request['id']);

            $automatedEmail->update([
                'email_template' => $request['email_template'],
                'email_address' => $request['email_address'],
                'frequency_interval' => $request['frequency_interval'],
                'frequency_unit' => $request['frequency_unit'],
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'automatedEmail' => $automatedEmail,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Automatizáció mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request) {

        try {
            $automatedEmail = AutomatedEmail::findOrFail($request->id);
            $automatedEmail->delete();

            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (\Exception $e) {

            \Log::error('Automatizáció törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }

    }
}
