<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create([
            'first_name' => 'Teszt',
            'last_name' => 'Elek',
            'email' => 'teszt.elek@mail.com',
            'password' => Hash::make('password'),
        ]);
    }
}
