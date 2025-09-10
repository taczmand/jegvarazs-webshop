<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewAppointment;
use App\Models\Appointment;
use App\Models\AppointmentPhoto;
use App\Models\ProductPhoto;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            'id',
            'name',
            'email',
            'phone',
            'zip_code',
            'city',
            'address_line',
            'appointment_date',
            'appointment_type',
            'message',
            'status',
            'created_at',
            'viewed_by',
            'viewed_at',
        ]);

        return DataTables::of($appointments)
            ->editColumn('created_at', function ($item) {
                return $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '';
            })
            ->addColumn('viewed_by', function ($item) {
                if ($item->viewed_by) {
                    $tooltip = $item->viewed_at
                        ? \Carbon\Carbon::parse($item->viewed_at)->format('Y-m-d H:i:s')
                        : '';
                    return '<span title="' . e($tooltip) . '">' . e($item->viewed_by) . '</span>';
                }
                return '<span class="text-warning"><i class="fa-solid fa-eye-slash"></i></span>';
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', '=', "{$keyword}");
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
            ->setRowClass(function ($item) {
                return $item->viewed_by ? '' : 'fw-bold'; // ha nincs viewed_by → vastag
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

            if ($request->input('email')) {
                Mail::to($request->input('email'))
                    ->bcc('jegvarazsiroda@gmail.com')
                    ->send(new NewAppointment($appointment));
            }

            if (!empty($request->file('new_photos'))) {

                $photos = [];

                foreach ($request->file('new_photos') as $file) {

                    $extension = strtolower($file->getClientOriginalExtension());

                    $filename = Str::random(40) . '.' . $extension;
                    $storagePath = 'appointment_images/' . $filename;

                    if (!Storage::disk('local')->exists($storagePath)) {
                        $file->storeAs('appointment_images', $filename, 'local');

                        // Teljes fájlút
                        $fullPath = Storage::disk('local')->path($storagePath);

                        // Csak képek optimalizálása (ne pdf/doc)
                        if (!in_array($extension, ['pdf', 'doc', 'docx'])) {
                            try {
                                $imagick = new \Imagick($fullPath);

                                // Tömörítési beállítások
                                if (in_array($extension, ['jpg', 'jpeg'])) {
                                    $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                                    $imagick->setImageCompressionQuality(75);
                                } elseif ($extension === 'png') {
                                    $imagick->setImageCompression(\Imagick::COMPRESSION_ZIP);
                                    $imagick->setImageCompressionQuality(75);
                                }

                                // Metaadatok törlése
                                $imagick->stripImage();

                                // Felülírás optimalizált változattal
                                $imagick->writeImage($fullPath);
                                $imagick->destroy();
                            } catch (\Exception $e) {
                                \Log::error("Kép optimalizálás sikertelen: {$fullPath} - {$e->getMessage()}");
                            }
                        }

                        $photos[] = [
                            'path' => $filename
                        ];
                    }
                }
                if (!empty($photos)) {
                    $appointment->photos()->createMany($photos);
                }

            }

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

            if (!empty($request->file('new_photos'))) {

                $photos = [];

                foreach ($request->file('new_photos') as $file) {

                    $extension = strtolower($file->getClientOriginalExtension());

                    $filename = Str::random(40) . '.' . $extension;
                    $storagePath = 'appointment_images/' . $filename;

                    if (!Storage::disk('local')->exists($storagePath)) {
                        $file->storeAs('appointment_images', $filename, 'local');

                        // Teljes fájlút
                        $fullPath = Storage::disk('local')->path($storagePath);

                        // Csak képek optimalizálása (ne pdf/doc)
                        if (!in_array($extension, ['pdf', 'doc', 'docx'])) {
                            try {
                                $imagick = new \Imagick($fullPath);

                                // Tömörítési beállítások
                                if (in_array($extension, ['jpg', 'jpeg'])) {
                                    $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                                    $imagick->setImageCompressionQuality(75);
                                } elseif ($extension === 'png') {
                                    $imagick->setImageCompression(\Imagick::COMPRESSION_ZIP);
                                    $imagick->setImageCompressionQuality(75);
                                }

                                // Metaadatok törlése
                                $imagick->stripImage();

                                // Felülírás optimalizált változattal
                                $imagick->writeImage($fullPath);
                                $imagick->destroy();
                            } catch (\Exception $e) {
                                \Log::error("Kép optimalizálás sikertelen: {$fullPath} - {$e->getMessage()}");
                            }
                        }

                        $photos[] = [
                            'path' => $filename
                        ];
                    }
                }
                if (!empty($photos)) {
                    $appointment->photos()->createMany($photos);
                }

            }

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

            // Először töröljük a képeket
            foreach ($appointment->photos as $photo) {
                Storage::disk('local')->delete('appointment_images/' . $photo->path);
                $photo->delete();
            }

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

    public function show($id) {
         $appointment = Appointment::with('photos')->findOrFail($id);
         return response()->json($appointment);
    }

    public function deleteAppointmentPhoto(Request $request) {
        try {
            $photo = AppointmentPhoto::findOrFail($request->id);

            Storage::disk('local')->delete('appointment_images/' . $photo->path);
            $photo->delete();

            return response()->json([
                'message' => 'Sikeres törlés!',
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Időpont kép törlési hiba: ' . $e->getMessage());

            return response()->json([
                'message' => 'Hiba történt a törlés során.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
