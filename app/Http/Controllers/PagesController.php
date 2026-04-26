<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use App\Mail\NewAppointment;
use App\Models\Appointment;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CompanySite;
use App\Models\Download;
use App\Models\Employee;
use App\Models\Lead;
use App\Models\NewsletterSubscription;
use App\Models\Product;
use App\Models\Searched;
use App\Services\Ai\SearchQueryAssistant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PagesController extends Controller
{
    public function index() {
        $all_categories = Category::with([
            'products' => function ($q) {
                $q->with(['photos' => function ($q2) {
                    $q2->where('is_main', true);
                }]);
            }
        ])
            ->active()
            ->orderBy('title', 'asc')
            ->get();


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

    public function offer() {

        return view('pages.offer');
    }

    public function product() {

        $product = Product::where('slug', $slug)->firstOrFail();

        return view('pages.products.show', compact('product'));
    }

    public function addAppointment(Request $request)
    {
        $token = $request->input('g-recaptcha-response');

        $response = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret'   => '6Le1aA4sAAAAAPQCX01qczEUwunjqGc_tTx_SNKa',
                'response' => $token,
                'remoteip' => $request->ip(),
            ]
        );

        $data = $response->json();

        if (!($data['success'] ?? false) || ($data['score'] ?? 0) < 0.5) {
            return response()->json([
                'result' => 'error',
                'error_message' => "Nem sikerült az űrlap elküldése, kérem próbálja újra. (reCAPTCHA hiba vagy BOT)"
            ]);
        }

        // Validáció
        $request->validate([
            'name'  => ['required', 'string', 'max:255', 'regex:/^(?!.*\d).+$/u'],
            'email' => 'required|email|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+\s().-]+$/',
                function ($attribute, $value, $fail) {
                    $digitsOnly = preg_replace('/\D+/', '', (string) $value);
                    $length = strlen($digitsOnly);

                    if ($length < 9 || $length > 15) {
                        $fail('A telefonszám nem megfelelő.');
                    }
                },
            ],
        ], [
            'name.regex' => 'A név nem tartalmazhat számot.',
            'phone.regex' => 'A telefonszám formátuma nem megfelelő.',
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

            if ($request->appointment_type == 'Érdeklődés' || $request->appointment_type == 'Felmérés') {
                Lead::create([
                    'lead_id' => Str::uuid(),
                    'form_id' => '1',
                    'form_name' => 'Weboldalról érdeklődés',
                    'full_name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'city' => $request->city,
                    'status' => 'Új',
                    'comment' => $request->message,
                    'data' => json_encode(array("field_data" =>  null))
                ]);
            }

            $mail = Mail::to($request->email);
            if (!app()->environment('local')) {
                $mail->bcc('jegvarazsiroda@gmail.com');
            }
            $mail->send(new NewAppointment($appointment));

        } catch (\Exception $e) {
            return response()->json(['result' => 'error', 'error_message' => $e->getMessage()], 200);
        }

        return redirect()->back()->with('success', 'Az időpontfoglalás sikeresen elküldve!');
    }

    public function addOffer(Request $request) {
        $token = $request->input('g-recaptcha-response');

        $response = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret'   => '6Le1aA4sAAAAAPQCX01qczEUwunjqGc_tTx_SNKa',
                'response' => $token,
                'remoteip' => $request->ip(),
            ]
        );

        $data = $response->json();

        if (!($data['success'] ?? false) || ($data['score'] ?? 0) < 0.5) {
            return response()->json([
                'result' => 'error',
                'error_message' => "Nem sikerült az űrlap elküldése, kérem próbálja újra. (reCAPTCHA hiba vagy BOT)"
            ]);
        }

        // Validáció
        $request->validate([
            'name'  => ['required', 'string', 'max:255', 'regex:/^(?!.*\d).+$/u'],
            'email' => 'required|email|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+\s().-]+$/',
                function ($attribute, $value, $fail) {
                    $digitsOnly = preg_replace('/\D+/', '', (string) $value);
                    $length = strlen($digitsOnly);

                    if ($length < 9 || $length > 15) {
                        $fail('A telefonszám nem megfelelő.');
                    }
                },
            ],
        ], [
            'name.regex' => 'A név nem tartalmazhat számot.',
            'phone.regex' => 'A telefonszám formátuma nem megfelelő.',
        ]);

        try {

            Lead::create([
                'lead_id' => Str::uuid(),
                'form_id' => '1',
                'form_name' => 'Weboldalról ajánlatkérés',
                'full_name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'city' => $request->city,
                'status' => 'Új',
                'comment' => $request->message,
                'data' => json_encode(array("field_data" =>  null))
            ]);

        } catch (\Exception $e) {
            return response()->json(['result' => 'error', 'error_message' => $e->getMessage()], 200);
        }

        return redirect()->back()->with('success', 'Az ajánlatkérés sikeresen elküldve!');
    }

    public function search(Request $request) {
        $query = $request->input('query');

        if (!$query) {
            return redirect()->back()->with('error', 'Kérjük, adjon meg keresési kifejezést.');
        }

        $originalQuery = trim((string) $query);
        $originalTokens = preg_split('/\s+/u', $originalQuery) ?: [];
        $originalTokens = array_values(array_filter(array_map('trim', $originalTokens), fn ($t) => $t !== ''));

        // Ha a keresésben van rövid, csupa nagybetűs token (márkanév / rövidítés gyanús),
        // akkor azt kötelező feltételként kezeljük, hogy ne "szélesítse ki" az AI az eredményeket.
        $mandatoryToken = null;
        foreach ($originalTokens as $tok) {
            if (preg_match('/^[A-Z0-9]{2,6}$/u', $tok)) {
                $mandatoryToken = $tok;
                break;
            }
        }

        $assistant = app(SearchQueryAssistant::class);
        $categories = Category::query()->pluck('title')->all();
        $enriched = $assistant->enrich((string) $query, [
            'categories' => $categories,
        ]);

        $keywords = $enriched['keywords'] ?? [];
        if (!is_array($keywords) || $keywords === []) {
            $keywords = [trim((string) $query)];
        }

        $must = $enriched['must'] ?? [];
        if (!is_array($must)) {
            $must = [];
        }

        $should = $enriched['should'] ?? [];
        if (!is_array($should)) {
            $should = [];
        }

        // A felhasználó által begépelt rövid, nagybetűs token (pl. AUX) legyen kötelező találat.
        if (is_string($mandatoryToken) && trim($mandatoryToken) !== '') {
            array_unshift($must, trim($mandatoryToken));
        }

        $must = array_values(array_unique(array_filter(array_map('strval', $must), fn ($v) => trim($v) !== '')));
        $should = array_values(array_unique(array_filter(array_map('strval', $should), fn ($v) => trim($v) !== '')));

        $brand = $enriched['brand'] ?? null;
        $brand = is_string($brand) && trim($brand) !== '' ? trim($brand) : null;

        $attributeFilters = $enriched['attribute_filters'] ?? [];
        if (!is_array($attributeFilters)) {
            $attributeFilters = [];
        }

        $categoryTitle = $enriched['category'] ?? null;
        $categoryId = null;
        if (is_string($categoryTitle) && $categoryTitle !== '') {
            $categoryId = Category::query()->where('title', $categoryTitle)->value('id');
        }

        $buildQueryProducts = function (?int $categoryIdFilter, ?string $brandFilter) use ($keywords, $mandatoryToken, $must, $should, $brand, $attributeFilters) {
            $queryProducts = Product::where('status', 'active');

            if ($categoryIdFilter) {
                $queryProducts->where('cat_id', $categoryIdFilter);
            }

            $resolvedBrand = $brandFilter ?? $brand;
            if ($resolvedBrand) {
                $queryProducts->whereHas('brands', function ($q) use ($resolvedBrand) {
                    $q->where('title', 'like', '%' . $resolvedBrand . '%');
                });
            }

            if ($attributeFilters !== []) {
                foreach ($attributeFilters as $f) {
                    if (!is_array($f)) {
                        continue;
                    }
                    $attrName = isset($f['name']) && is_string($f['name']) ? trim($f['name']) : '';
                    if ($attrName === '') {
                        continue;
                    }
                    $attrValue = null;
                    if (array_key_exists('value', $f) && is_string($f['value'])) {
                        $attrValue = trim($f['value']);
                        if ($attrValue === '') {
                            $attrValue = null;
                        }
                    }

                    $queryProducts->whereHas('attributes', function ($q) use ($attrName, $attrValue) {
                        $q->where('attributes.name', 'like', '%' . $attrName . '%');
                        if ($attrValue !== null) {
                            $q->where('product_attributes.value', 'like', '%' . $attrValue . '%');
                        }
                    });
                }
            }

            // MUST: minden kifejezésnek illeszkednie kell legalább egy mezőre
            if ($must !== []) {
                foreach ($must as $mt) {
                    $mt = trim((string) $mt);
                    if ($mt === '') {
                        continue;
                    }
                    $queryProducts->where(function ($q) use ($mt) {
                        $q->where('title', 'like', '%' . $mt . '%')
                            ->orWhere('description', 'like', '%' . $mt . '%')
                            ->orWhereHas('brands', function ($qb) use ($mt) {
                                $qb->where('title', 'like', '%' . $mt . '%');
                            })
                            ->orWhereHas('tags', function ($q2) use ($mt) {
                                $q2->where('name', 'like', '%' . $mt . '%');
                            })
                            ->orWhereHas('attributes', function ($q3) use ($mt) {
                                $q3->where('attributes.name', 'like', '%' . $mt . '%')
                                    ->orWhere('product_attributes.value', 'like', '%' . $mt . '%');
                            });
                    });
                }
            }

            // SHOULD: csak "rásegítés". Ha van MUST, akkor ne szélesítsen - külön blokkban marad.
            if ($should !== []) {
                $queryProducts->where(function ($q) use ($should, $mandatoryToken) {
                    foreach ($should as $kw) {
                        if (!is_string($kw) || trim($kw) === '') {
                            continue;
                        }
                        $kw = trim($kw);
                        if (is_string($mandatoryToken) && $mandatoryToken !== '' && strcasecmp($kw, $mandatoryToken) === 0) {
                            continue;
                        }
                        $q->orWhere('title', 'like', '%' . $kw . '%')
                            ->orWhere('description', 'like', '%' . $kw . '%')
                            ->orWhereHas('brands', function ($qb) use ($kw) {
                                $qb->where('title', 'like', '%' . $kw . '%');
                            })
                            ->orWhereHas('tags', function ($q2) use ($kw) {
                                $q2->where('name', 'like', '%' . $kw . '%');
                            })
                            ->orWhereHas('attributes', function ($q3) use ($kw) {
                                $q3->where('attributes.name', 'like', '%' . $kw . '%')
                                    ->orWhere('product_attributes.value', 'like', '%' . $kw . '%');
                            });
                    }
                });
            }

            // Ha nincs structured terv (MUST/SHOULD üres), maradjon a legacy keywords OR keresés
            if ($must === [] && $should === []) {
                $queryProducts->where(function ($q) use ($keywords) {
                    foreach ($keywords as $kw) {
                        if (!is_string($kw) || trim($kw) === '') {
                            continue;
                        }
                        $kw = trim($kw);
                        $q->orWhere('title', 'like', '%' . $kw . '%')
                            ->orWhere('description', 'like', '%' . $kw . '%')
                            ->orWhereHas('brands', function ($qb) use ($kw) {
                                $qb->where('title', 'like', '%' . $kw . '%');
                            })
                            ->orWhereHas('tags', function ($q2) use ($kw) {
                                $q2->where('name', 'like', '%' . $kw . '%');
                            })
                            ->orWhereHas('attributes', function ($q3) use ($kw) {
                                $q3->where('attributes.name', 'like', '%' . $kw . '%')
                                    ->orWhere('product_attributes.value', 'like', '%' . $kw . '%');
                            });
                    }
                });
            }

            return $queryProducts;
        };

        $queryProducts = $buildQueryProducts($categoryId, $brand);


        // Eredeti találatok száma
        $totalHits = $queryProducts->count();

        // Ha az AI által tippelt kategória lenullázta a találatokat, próbáljuk újra kategória szűrés nélkül
        if ($totalHits === 0) {
            // 1) először próbáljuk brand szűrés nélkül (gyakori, hogy nincs rendesen bekötve a brand)
            if ($brand) {
                $queryProducts = $buildQueryProducts($categoryId, null);
                $totalHits = $queryProducts->count();
            }

            // 2) ha még mindig 0 és van kategória szűrés, próbáljuk kategória nélkül
            if ($totalHits === 0 && $categoryId) {
                $categoryId = null;
                $queryProducts = $buildQueryProducts(null, $brand);
                $totalHits = $queryProducts->count();
            }

            // 3) ha még mindig 0 és volt brand/kategória, próbáljuk mindkettő nélkül
            if ($totalHits === 0 && ($brand || $categoryId)) {
                $queryProducts = $buildQueryProducts(null, null);
                $totalHits = $queryProducts->count();
            }
        }

        // Paginate
        $products = $queryProducts->paginate(12)->appends(['query' => $query]);

        Searched::create([
            'search_term' => $query,
            'number_of_hits' => $totalHits,
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
            if ($request->input('contact_email')) {
                $contactData = [
                    'contact_name' => $request->input('contact_name'),
                    'contact_email' => $request->input('contact_email'),
                    'contact_message' => $request->input('contact_message'),
                ];

                if (!app()->environment('local')) {
                    Mail::to('jegvarazsiroda@gmail.com')
                        ->send(new ContactMessage($contactData));
                }
            }

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
