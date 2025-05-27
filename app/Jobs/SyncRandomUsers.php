<?php

namespace App\Jobs;

use App\Services\RandomApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncRandomUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        
    }

    /**
     * Execute the job.
     */
    public function handle(RandomApiService $randomApi): void
    {
         Log::info('SyncRandomUsers started');
        try {
            $entidades = $randomApi->fetchAndUpdateUsers();
            
            
            foreach ($entidades as $entidad) {
                
                 $user = \App\Models\User::firstOrNew(['rut' => $entidad['KOEN'] ?? null]);

                $user->name          = $entidad['NOKOEN'] ?? '';
                $user->email         = $entidad['EMAIL'] ?? null;
                $user->business_name = $entidad['SIEN'] ?? '';
                $user->is_active     = true;
                $user->phone         = $entidad['FOEN'] ?? null;

                // Solo asigna password si es un usuario nuevo
                if (!$user->exists) {
                    $user->password = bcrypt('password');
                }

                $user->save();
            
            }
            Log::info('SyncRandomUsers completed successfully');
        } catch (\Exception $e) {
            Log::error('SyncRandomUsers failed: ' . $e->getMessage());
        }
    }
}
