<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $fakeBrands = $this->getFakeBrands();
        foreach ($fakeBrands as $fb) {
            Brand::create([
                'name' => $fb->name,
                'description' => $fb->description,
                'logo_url' => $fb->logo_url,
            ]);
        }
    }

    private function getFakeBrands()
    {
        $brandsJsonFile = Storage::disk('local')->get('fake_seed_data/brands.json');
        $brands = json_decode($brandsJsonFile);
        return $brands;
    }
}
