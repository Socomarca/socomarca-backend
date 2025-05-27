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
        // Usuarios fijos
        $usuariosFijos = [
            ['name' => 'Juan Perez', 'email' => '23843925-6@socomarca.cl', 'rut' => '23843925-6'],
            ['name' => 'Pedro Neza', 'email' => '19285517-9@socomarca.cl', 'rut' => '19285517-9'],
            ['name' => 'Armando Meza', 'email' => '13462207-5@socomarca.cl', 'rut' => '13462207-5'],
            ['name' => 'Rogelio Rojas', 'email' => '12532299-9@socomarca.cl', 'rut' => '12532299-9'],
            ['name' => 'Eduardo Fuentes', 'email' => '20285838-4@socomarca.cl', 'rut' => '20285838-4'],
            ['name' => 'Maria Tapia ', 'email' => '12312312-3@socomarca.cl', 'rut' => '12312312-3'],
            
        ];

        foreach ($usuariosFijos as $usuario) {
            User::updateOrCreate(
                ['rut' => $usuario['rut']],
                [
                    'name' => $usuario['name'],
                    'email' => $usuario['email'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
        }

        // Usuarios aleatorios
        
        User::factory()->count(5)
            ->has(Address::factory()->count(2), 'addresses')
                ->create();
    }
}
