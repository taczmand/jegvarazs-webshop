<?php

namespace App\Http\Controllers;

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
}
