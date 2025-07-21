<?php

namespace App\Http\Controllers;

use App\Models\Siteinfo;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    // Muestra la configuración actual
    public function index()
    {
        $settings = Siteinfo::where('key', 'prices_settings')->first();
        return response()->json([
            'min_max_quantity_enabled' => $settings ? ($settings->value['min_max_quantity_enabled'] ?? false) : false,
        ]);
    }

    // Actualiza la configuración
    public function update(Request $request)
    {
        $data = $request->validate([
            'min_max_quantity_enabled' => 'required|boolean',
        ]);

        Siteinfo::updateOrCreate(
            ['key' => 'prices_settings'],
            ['value' => $data]
        );

        return response()->json(['message' => 'Configuración actualizada correctamente']);
    }




}
