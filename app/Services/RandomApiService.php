<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RandomApiService
{
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $ttl;

    public function __construct()
    {
        $this->baseUrl = 'http://seguimiento.random.cl:3003';
        $this->username = 'demo@random.cl';
        $this->password = 'd3m0r4nd0m3RP';
        $this->ttl = 10;
    }

    protected function getToken()
    {
        return Cache::remember('random_api_token', $this->ttl * 60, function () {
            $response = Http::asForm()->post($this->baseUrl . '/login', [
                'username' => $this->username,
                'password' => $this->password,
                'ttl' => $this->ttl
            ]);

            if ($response->successful()) {
                return $response->json()['token'];
            }

            $error = $response->json();
            throw new \Exception('Error al obtener el token de autenticación: ' . $error['message']);
        });
    }

    protected function makeRequest($method, $endpoint, $params = [])
    {
        $token = $this->getToken();
        $response = Http::withHeaders([ 
            'Authorization' => 'Bearer ' . $token
        ])->$method($this->baseUrl . $endpoint, $params);


        //If token is expired, get new token and make request again
        if(isset($response->json()['message']) && $response->json()['message'] == 'jwt expired'){
            Cache::forget('random_api_token');
            $token = $this->getToken();
            $response = Http::withHeaders([ 
                'Authorization' => 'Bearer ' . $token
            ])->$method($this->baseUrl . $endpoint, $params);
                
            return $response->json();
        }

        return $response->json();


    }

    public function getEntidades($empresa, $kofu, $modalidad, $size = 5, $page = 1)
    {
        return $this->makeRequest('get', '/web32/entidades', [
            'empresa' => $empresa,
            'kofu' => $kofu,
            'modalidad' => $modalidad,
            'size' => $size,
            'page' => $page
        ]);
    }

    public function getProducts($tipr = '', $kopr_anterior = 0, $kopr = '', $nokopr = '', $search = '', $fmpr = '', $pfpr = '', $hfpr = '')
    {
   
        $data = [
            'kopr_anterior' => $kopr_anterior,
            'kopr' => $kopr,
            'nokopr' => $nokopr,
            'search' => $search,
            'fmpr' => $fmpr,
            'pfpr' => $pfpr,
            'hfpr' => $hfpr,
            'fields' => "KOPR,NOKOPR,KOPRAL,NMARCA"
        ];

        if (!empty($tipr)) {
            $data['tipr'] = $tipr;
        }

        return $this->makeRequest('get', '/productos?' . http_build_query($data));
    }

    public function getCategories()
    {
        return $this->makeRequest('get', '/familias');
    }

    public function getPricesLists(){

        $data = [
            'empresa' => '01',
        ];
        $request = $this->makeRequest('get', '/web32/precios/pidelistaprecio?' . http_build_query($data));
        return $request;
    }
} 