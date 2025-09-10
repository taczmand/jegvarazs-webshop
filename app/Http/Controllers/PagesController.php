<?php

namespace App\Http\Controllers;

use App\Mail\NewAppointment;
use App\Models\Appointment;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CompanySite;
use App\Models\Download;
use App\Models\Employee;
use App\Models\NewsletterSubscription;
use App\Models\Product;
use App\Models\Searched;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;

class PagesController extends Controller
{
    public function index() {
        $all_categories = Category::with([
            'products' => function ($q) {
                $q->with(['photos' => function ($q2) {
                    $q2->where('is_main', true);
                }]);
            }
        ])->active()->get();

        $last_blogs = BlogPost::latest()
            ->where('status', 'published')
            ->take(3)
            ->get();

        $brands = Brand::all();

        return view('pages.index', [
            'all_categories' => $all_categories,
            'last_blogs' => $last_blogs,
            'brands' => $brands
        ]);

    }

    public function blog() {
        $blogs = BlogPost::where('status', 'published')->latest()->paginate(10);

        return view('pages.blog.index', compact('blogs'));
    }

    public function blogPost($slug) {
        $blog = BlogPost::where('slug', $slug)->firstOrFail();

        return view('pages.blog.show', compact('blog'));
    }

    public function about() {

        return view('pages.about');
    }

    public function contact() {
        $employees = Employee::all();
        $company_sites = CompanySite::all();
        return view('pages.contact', compact('employees', 'company_sites'));
    }

    public function downloads() {
        $downloads = Download::where('status', 'active')->get();
        return view('pages.downloads', compact('downloads'));
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

            $appointment = Appointment::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'zip_code' => $request->zip_code,
                'city' => $request->city,
                'address_line' => $request->address_line,
                'appointment_type' => $request->appointment_type,
                'message' => $request->message
            ]);

            Mail::to($request->email)
                ->bcc('jegvarazsiroda@gmail.com')
                ->send(new NewAppointment($appointment));

        } catch (\Exception $e) {
            return response()->json(['result' => 'error', 'error_message' => $e->getMessage()], 200);
        }

        return redirect()->back()->with('success', 'Az időpontfoglalás sikeresen elküldve!');
    }

    public function search(Request $request) {
        $query = $request->input('query');

        if (!$query) {
            return redirect()->back()->with('error', 'Kérjük, adjon meg keresési kifejezést.');
        }

        $products = Product::where('title', 'like', '%' . $query . '%')->where('status', true)
            ->orWhere('description', 'like', '%' . $query . '%')
            ->paginate(10)
            ->appends(['query' => $query]);


        Searched::create([
            'search_term' => $query,
            'number_of_hits' => $products->count(),
            'ip_address' => $request->ip()
        ]);

        return view('pages.search_results', compact('products', 'query'));
    }

    public function newSubscription(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255'
        ]);

        // Ellenőrizzük, hogy a felhasználó nem adta-e meg az email címét
        if (!$request->email) {
            return response()->json(['result' => 'error', 'error_message' => 'Kérjük, adja meg az email címét.'], 200);
        }
        // Ellenőrizzük, hogy az email cím formátuma helyes-e
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['result' => 'error', 'error_message' => 'Kérjük, adjon meg egy érvényes email címet.'], 200);
        }

        // Ellenőrizzük, hogy a felhasználó már fel van-e iratkozva
        $existingSubscription = NewsletterSubscription::where('email', $request->email)->first();
        if ($existingSubscription) {
            return response()->json(['result' => 'error', 'error_message' => 'Ez az email cím már fel van iratkozva a hírlevélre.'], 200);
        }

        try {
            NewsletterSubscription::create([
                'email' => $request->email
            ]);
            return response()->json(['result' => 'success', 'message' => 'Sikeresen feliratkozott a hírlevélre!'], 200);
        } catch (\Exception $e) {
            return response()->json(['result' => 'error', 'error_message' => $e->getMessage()], 200);
        }
    }

    public function newContactForm(Request $request)
    {
        $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_message' => 'required|string|max:65000'
        ]);
        // Ellenőrizzük, hogy a felhasználó nem adta-e meg az email címét
        if (!$request->contact_email) {
            return response()->json(['result' => 'error', 'error_message' => 'Kérjük, adja meg az email címét.'], 200);
        }
        // Ellenőrizzük, hogy az email cím formátuma helyes-e
        if (!filter_var($request->contact_email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['result' => 'error', 'error_message' => 'Kérjük, adjon meg egy érvényes email címet.'], 200);
        }

        try {
            // email küldése a megadott címre
            \Log::info($request->all());
            return response()->json(['result' => 'success', 'message' => 'Sikeresen elküldve!'], 200);
        } catch (\Exception $e) {
            return response()->json(['result' => 'error', 'error_message' => $e->getMessage()], 200);
        }
    }

    public function incognito() {
        Cookie::queue('incognito_mode', 'jegvarazs', 60 * 24 * 60);
        return redirect('/');
    }
}
