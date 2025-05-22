<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $fakeCategories = $this->getFakeCategories();

        foreach ($fakeCategories as $fc) {
            $category = Category::create([
                'name' => $fc->name,
                'description' => $fc->description,
            ]);

            foreach ($fc->subcategories as $sc) {
                $category->subcategories()->create([
                    'name' => $sc->name,
                    'description' => $sc->description,
                ]);
            }
        }
    }

    public function getFakeCategories()
    {
        $categoriesJsonFile = Storage::disk('local')->get('fake_seed_data/categories.json');
        $categories = json_decode($categoriesJsonFile);
        return $categories;
    }
}
