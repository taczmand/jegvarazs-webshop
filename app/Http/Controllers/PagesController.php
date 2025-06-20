<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function index() {

        return view('pages.index');
    }

    public function about() {

        return view('pages.about');
    }

    public function contact() {

        return view('pages.contact');
    }

    public function downloads() {

        return view('pages.downloads');
    }

    public function appointment() {

        return view('pages.appointment');
    }

    public function product() {

        $product = Product::where('slug', $slug)->firstOrFail();

        return view('pages.products.show', compact('product'));
    }

    public function addAppointment(Request $request) {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20'
        ]);

        try {

            Appointment::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'zip_code' => $request->zip_code,
                'city' => $request->city,
                'address_line' => $request->address_line,
                'appointment_type' => $request->appointment_type,
                'message' => $request->message
            ]);

            // TODO: Send confirmation email to the user

        } catch (\Exception $e) {
            return response()->json(['result' => 'error', 'error_message' => $e->getMessage()], 200);
        }

        return redirect()->back()->with('success', 'Az időpontfoglalás sikeresen elküldve!');
    }
}
