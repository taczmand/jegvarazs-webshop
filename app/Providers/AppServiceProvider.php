<?php

namespace App\Providers;

use App\Models\BasicData;
use App\Models\Category;
use App\Models\Regulation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*DB::listen(function($query) {
            \Log::info(
                $query->sql,
                $query->bindings,
                $query->time
            );
        });*/


        if (!request()->is('admin/*')) {
            View::share('categories', Category::with(['children' => function($query) {
                $query->where('status', 'active');
            }])->whereNull('parent_id')->where('status', 'active')->get());

            View::share('basicdata', BasicData::all()->pluck('value', 'key')->toArray());
            View::share('regulations', Regulation::active()->get());
        }
    }
}
