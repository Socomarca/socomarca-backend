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
                'code' => fake()->regexify('[A-Z]{10}'),
                'level' => fake()->numberBetween(1, 10),
                'key' => fake()->regexify('[A-Z]{4}'),
            ]);

            foreach ($fc->subcategories as $sc) {
                $category->subcategories()->create([
                    'name' => $sc->name,
                    'description' => $sc->description,
                    'code' => fake()->regexify('[A-Z]{10}'),
                    'level' => fake()->numberBetween(1, 10),
                    'key' => fake()->regexify('[A-Z]{4}'),
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
