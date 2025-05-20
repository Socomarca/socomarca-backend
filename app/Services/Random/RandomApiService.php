<?php

namespace App\Services\Random;

use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RandomApiService
{
    public function fetchAndUpdateUsers()
    {
        // 1. Login para obtener el token
        $loginResponse = Http::post('http://seguimiento.random.cl:3003/login', [
            'username' => 'demo@random.cl',
            'password' => 'd3m0r4nd0m3RP'
        ]);

        if (!$loginResponse->successful()) {
            Log::error('Error autenticando con Random API');
            return;
        }

        $token = $loginResponse->json('token');

        // 2. Usar el token en la petición GET
        $response = Http::withToken($token)
            ->get('http://seguimiento.random.cl:3003/web32/entidades', [
                'empresa' => '01',
                'size' => 2
            ]);

        if ($response->successful()) {
            $entities = $response->json();

            // Listar el retorno (puedes usar Log o dd para debug)
            Log::info('Entidades recibidas:', $entities);

            foreach ($entities as $entity) {
                // User::updateOrCreate(
                //     ['external_id' => $entity['IDMAEEN'] ?? null], // Usa el campo único adecuado
                //     [
                //         'name' => $entity['NOKOEN'] ?? '',
                //         'email' => $entity['EMAIL'] ?? '',
                //         // Agrega aquí otros campos relevantes de tu modelo User
                //         // Ejemplo:
                //         // 'rut' => $entity['KOEN'] ?? '',
                //         // 'direccion' => $entity['DIEN'] ?? '',
                //     ]
                // );
            }
        } else {
            Log::error('Error obteniendo entidades de Random API');
        }
    }
}