<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BasicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            ['key' => 'support_email'],
            ['key' => 'support_phone'],
            ['key' => 'company_name'],
            ['key' => 'company_address'],
            ['key' => 'company_address_maps_link'],
            ['key' => 'company_phone'],
            ['key' => 'company_appointment_email'],
            ['key' => 'company_appointment_phone'],
            ['key' => 'company_vat_number'],
            ['key' => 'company_bank_account'],
            ['key' => 'company_bank_name'],
            ['key' => 'company_bank_swift'],
            ['key' => 'company_bank_iban'],
            ['key' => 'company_logo'],
            ['key' => 'company_favicon'],
            ['key' => 'company_footer_text'],
            ['key' => 'social_facebook'],
            ['key' => 'social_instagram'],
            ['key' => 'social_twitter'],
            ['key' => 'social_linkedin'],
            ['key' => 'social_youtube'],
            ['key' => 'social_tiktok'],
            ['key' => 'social_pinterest'],
            ['key' => 'social_whatsapp'],
            ['key' => 'social_telegram'],
            ['key' => 'social_viber'],
            ['key' => 'social_snapchat'],
            ['key' => 'social_twitch'],
            ['key' => 'header_message'],
            ['key' => 'hero_top_title'],
            ['key' => 'hero_main_title'],
            ['key' => 'hero_subtitle'],
            ['key' => 'hero_button_text'],
            ['key' => 'items_per_page'],
            ['key' => 'maintenance_mode'],
            ['key' => 'maintenance_message'],
            ['key' => 'cart_expiration_days'],
            ['key' => 'about_description'],
        ];

        foreach ($data as &$item) {
            $item['value'] = null;
            $item['created_at'] = $now;
            $item['updated_at'] = $now;
        }

        DB::table('basic_data')->insert($data);
    }
}
