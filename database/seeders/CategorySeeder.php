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
                'code' => rand(1, 100),
                'level' => 1,
                'key' => substr($fc->name, 0, 3),
            ]);

            foreach ($fc->subcategories as $sc) {
                $category->subcategories()->create([
                    'name' => $sc->name,
                    'description' => $sc->description,
                    'code' => rand(1, 100),
                    'level' => 2,
                    'key' => substr($fc->name, 0, 3).'/'.substr($sc->name, 0, 3),
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
