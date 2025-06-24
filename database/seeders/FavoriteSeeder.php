<?php

namespace Database\Seeders;

use App\Models\Favorite;
use App\Models\FavoriteList;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Ejecuta el seeder de listas de favoritos y favoritos.
     */
    public function run(): void
    {

        $users = User::all();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->error('No hay usuarios o productos disponibles para crear listas de favoritos y favoritos.');
            return;
        }

        $favoriteListNames = [
            'Favoritos',
            'Lista de deseos',
            'Productos favoritos',
            'Mis preferidos',
            'Comprar después',
            'Lista principal',
            'Productos guardados',
            'Mi selección',
            'Para comprar',
            'Lista especial'
        ];

        $createdLists = [];

        foreach ($users as $user) {
            $numLists = rand(1, 3);
            $usedNames = [];

            for ($i = 0; $i < $numLists; $i++) {

                $availableNames = array_diff($favoriteListNames, $usedNames);

                if (empty($availableNames)) {
                    break;
                }

                $listName = $availableNames[array_rand($availableNames)];
                $usedNames[] = $listName;

                $favoriteList = FavoriteList::create([
                    'name' => $listName,
                    'user_id' => $user->id,
                ]);

                $createdLists[] = $favoriteList;
            }
        }

        $totalFavorites = 0;
        foreach ($createdLists as $favoriteList) {
            $numFavorites = rand(3, 8);
            $usedProducts = [];

            for ($i = 0; $i < $numFavorites; $i++) {

                $availableProducts = $products->whereNotIn('id', $usedProducts);

                if ($availableProducts->isEmpty()) {
                    break;
                }

                $product = $availableProducts->random();
                $usedProducts[] = $product->id;

                $price = $product->prices()->first();


                Favorite::create([
                    'favorite_list_id' => $favoriteList->id,
                    'product_id' => $product->id,
                    'unit' => $price->unit,
                ]);

                $totalFavorites++;
            }
        }

    }
}
