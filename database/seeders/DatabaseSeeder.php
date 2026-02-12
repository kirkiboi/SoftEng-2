<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed a test user
        User::create([
            'first_name' => 'Test',
            'last_name'  => 'User',
            'email'      => 'test@example.com',
            'password'   => Hash::make('password'),
            'role'       => 'admin',
        ]);

        // Seed Ingredients (categories must match DB enum exactly — all lowercase)
        $oil = \App\Models\Ingredient::create([
            'name'          => 'Cooking Oil',
            'category'      => 'oils',
            'unit'          => 'ml',
            'cost_per_unit' => 0.05,
            'stock'         => 1000,
            'threshold'     => 100,
        ]);

        $salt = \App\Models\Ingredient::create([
            'name'          => 'Salt',
            'category'      => 'spices',
            'unit'          => 'g',
            'cost_per_unit' => 0.01,
            'stock'         => 5000,
            'threshold'     => 500,
        ]);

        $chicken = \App\Models\Ingredient::create([
            'name'          => 'Whole Chicken',
            'category'      => 'condiments',
            'unit'          => 'kg',
            'cost_per_unit' => 5.00,
            'stock'         => 50,
            'threshold'     => 5,
        ]);

        // Seed Product
        $friedChicken = \App\Models\Product::create([
            'name'     => 'Fried Chicken',
            'category' => 'meals',
            'price'    => 12.50,
            'image'    => 'products/fried-chicken.jpg',
        ]);

        // Seed Recipes — using integer batch_size (no more batch_sizes table)
        // Recipe for 10 servings
        \App\Models\Recipe::create([
            'product_id'    => $friedChicken->id,
            'batch_size'    => 10,
            'ingredient_id' => $oil->id,
            'quantity'      => 100, // 100ml oil
        ]);
        \App\Models\Recipe::create([
            'product_id'    => $friedChicken->id,
            'batch_size'    => 10,
            'ingredient_id' => $chicken->id,
            'quantity'      => 2, // 2kg chicken
        ]);

        // Recipe for 20 servings
        \App\Models\Recipe::create([
            'product_id'    => $friedChicken->id,
            'batch_size'    => 20,
            'ingredient_id' => $oil->id,
            'quantity'      => 250, // 250ml oil
        ]);
        \App\Models\Recipe::create([
            'product_id'    => $friedChicken->id,
            'batch_size'    => 20,
            'ingredient_id' => $chicken->id,
            'quantity'      => 4, // 4kg chicken
        ]);
    }
}
