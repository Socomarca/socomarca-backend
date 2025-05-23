<?php

namespace App\Jobs;

use App\Models\Price;
use App\Services\RandomApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncRandomPrices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(RandomApiService $randomApi)
    {
        Log::info('SyncRandomPrices started');
        try {
            $prices = $randomApi->getPricesLists();
            
            foreach($prices['datos'] as $price) {
                foreach($price['unidades'] as $unit) {
                    $data = [
                        'product_id' => $price['kopr'],
                        'price_list_id' => $prices['nombre'],
                        'unit' => $unit['nombre'],
                        'price' => $unit['prunneto'][0]['f'],
                        'valid_from' => null,
                        'valid_to' => null,
                        'is_active' => true,
                    ];
                    
                    Price::updateOrCreate([
                        'product_id' => $price['kopr'],
                        'price_list_id' => $prices['nombre'],
                        'unit' => $unit['nombre']
                    ], $data);
                }
            }
            
            Log::info('SyncRandomPrices finished');
        } catch (\Exception $e) {
            Log::error('Error sincronizando precios: ' . $e->getMessage());
            throw $e;
        }
    }
} 