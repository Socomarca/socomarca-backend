<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->count(5)
            ->has(Address::factory()->count(2), 'addresses')
                ->create();

        //Usuario personalizado
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'phone' => '1234567890',
            'rut' => '22375589-5',
            'business_name' => 'Admin',
            'is_active' => true,
            'last_login' => now(),
        ]);
    }
}
