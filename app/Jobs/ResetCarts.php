<?php

namespace App\Jobs;

use App\Models\BasicData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ResetCarts implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $days = BasicData::where('key', 'cart_expiration_days')->value('value') ?? 1; // Alapértelmezett 1 nap, ha nincs beállítva

        $expirationDate = now()->subDays($days);
        $deletedCount = \DB::table('carts')
            ->where('updated_at', '<', $expirationDate)
            ->delete();

        \Log::info("ResetCarts job completed. Deleted {$deletedCount} carts older than {$days} days.");
    }
}
