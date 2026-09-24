<?php

namespace App\Services\Admin;

use App\Models\AutomatedEmail;
use Carbon\Carbon;

class AutomatedEmailScheduler
{
    /**
     * Összeszedi a mai napon küldendő emaileket.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getEmailsForToday()
    {
        $today = Carbon::today();

        return AutomatedEmail::all()->filter(function ($automation) use ($today) {

            // Egyszeri, időzített küldés (send_at)
            if (!is_null($automation->send_at)) {
                if (!is_null($automation->last_sent_at)) {
                    return false;
                }

                return Carbon::parse($automation->send_at)->startOfDay()->lessThanOrEqualTo($today);
            }

            // Ha még nem lett küldve: start_date vagy azonnal küldhető
            if (is_null($automation->last_sent_at)) {
                return true;
            }

            $last = Carbon::parse($automation->last_sent_at);
            $next = $this->calculateNextSend($last, $automation->frequency_unit, $automation->frequency_interval);

            // Ha a következő dátum ma vagy korábban van
            return $next->startOfDay()->lessThanOrEqualTo($today);
        });
    }

    public function nextSendAt(AutomatedEmail $automation): ?Carbon
    {
        if (!is_null($automation->send_at)) {
            return Carbon::parse($automation->send_at);
        }

        if (is_null($automation->last_sent_at)) {
            return null;
        }

        $last = Carbon::parse($automation->last_sent_at);

        return $this->calculateNextSend($last, (string) $automation->frequency_unit, (int) $automation->frequency_interval);
    }

    /**
     * Következő küldési dátum számítása.
     *
     * @param  \Carbon\Carbon  $lastSent
     * @param  string  $unit
     * @param  int  $interval
     * @return \Carbon\Carbon
     */
    protected function calculateNextSend($lastSent, $unit, $interval)
    {
        switch ($unit) {
            case 'naponta':
                return $lastSent->copy()->addDays($interval);
            case 'hetente':
                return $lastSent->copy()->addWeeks($interval);
            case 'havonta':
                return $lastSent->copy()->addMonths($interval);
            case 'évente':
                return $lastSent->copy()->addYears($interval);
            default:
                return $lastSent; // fallback
        }
    }
}

