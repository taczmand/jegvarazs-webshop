<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        return view('admin.business.appointments');
    }
    public function data()
    {
        $appointments = Appointment::select([
            'appointments.id',
            'appointments.name',
            'appointments.email',
            'appointments.phone',
            'appointments.zip_code',
            'appointments.city',
            'appointments.address_line',
            'appointments.appointment_date',
            'appointments.appointment_type',
            'appointments.message',
            'appointments.status',
            'appointments.created_at',
            'users.name as viewed_by_name',
            'actions.viewed_by',
        ])
            ->leftJoin('user_actions as actions', function ($join) {
                $join->on('appointments.id', '=', 'actions.model_id')
                    ->where('actions.model', '=', 'appointments');
            })
            ->leftJoin('users', 'actions.viewed_by', '=', 'users.id');

        return DataTables::of($appointments)
            ->editColumn('created_at', function ($item) {
                return $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '';
            })
            ->addColumn('viewed_by', function ($item) {
                return $item->viewed_by_name ?? '<span class="text-warning"><i class="fa-solid fa-eye-slash"></i></span>';
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('appointments.status', '=', "{$keyword}");
            })
            ->addColumn('action', function ($item) {
                $user = auth('admin')->user();
                $actions = '';

                if ($user && $user->can('edit-appointment')) {
                    $actions .= '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $item->id . '" title="Szerkesztés">
                            <i class="fas fa-edit"></i>
                        </button>';
                }

                if ($user && $user->can('delete-appointment')) {
                    $actions .= '
                        <button class="btn btn-sm btn-danger delete" data-id="' . $item->id . '" title="Törlés">
                            <i class="fas fa-trash"></i>
                        </button>';
                }

                return $actions;
            })
            ->rawColumns(['action', 'viewed_by'])
            ->make(true);
    }


    public function store(Request $request)
    {
        try {
            $appointment = Appointment::create([
                'name'             => $request->input('name'),
                'email'            => $request->input('email'),
                'phone'            => $request->input('phone'),
                'zip_code'         => $request->input('zip_code'),
                'city'             => $request->input('city'),
                'address_line'     => $request->input('address_line'),
                'appointment_date' => $request->input('appointment_date'),
                'appointment_type' => $request->input('appointment_type', 'Karbantartás'),
                'message'          => $request->input('message'),
                'status'           => $request->input('status', 'Függőben'),
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'appointment' => $appointment,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Időpont mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $appointment = Appointment::findOrFail($request->input('id'));

            $appointment->update([
                'name'             => $request->input('name'),
                'email'            => $request->input('email'),
                'phone'            => $request->input('phone'),
                'zip_code'         => $request->input('zip_code'),
                'city'             => $request->input('city'),
                'address_line'     => $request->input('address_line'),
                'appointment_date' => $request->input('appointment_date'),
                'appointment_type' => $request->input('appointment_type', 'Karbantartás'),
                'message'          => $request->input('message'),
                'status'           => $request->input('status', 'Függőben'),
            ]);

            return response()->json([
                'message' => 'Sikeres mentés!',
                'appointment' => $appointment,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Időpont mentési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a mentés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id) {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->delete();

            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Nem található a törölni kívánt időpont.',
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Időpont törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
