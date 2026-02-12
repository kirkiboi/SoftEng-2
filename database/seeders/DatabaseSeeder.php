<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\BatchSize; // Assuming this model exists
use App\Models\Recipe;    // Assuming this model exists

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate tables to start fresh
        User::truncate();
        Product::truncate();
        Ingredient::truncate();
        // Check if these tables exist before truncating if migrations are fresh, 
        // but 'migrate:refresh' handles it. Using DB::table for safety if Models aren't perfect.
        DB::table('batch_sizes')->truncate();
        DB::table('recipes')->truncate();
        DB::table('transaction_items')->truncate();
        DB::table('transactions')->truncate();
        DB::table('product_audit_logs')->truncate();
        DB::table('ingredient_audit_logs')->truncate();
        DB::table('kitchen_production_logs')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Create Default User
        User::factory()->create([
            'role' => 'admin',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // 2. Seed Ingredients (Pinoy Carenderia / University Dining) - ~50 Items
        $ingredients = [
            // Meat & Poultry
            ['name' => 'Pork Liempo', 'category' => 'meat', 'unit' => 'kg', 'cost' => 350, 'stock' => 20, 'threshold' => 5],
            ['name' => 'Pork Kasim', 'category' => 'meat', 'unit' => 'kg', 'cost' => 320, 'stock' => 20, 'threshold' => 5],
            ['name' => 'Ground Pork', 'category' => 'meat', 'unit' => 'kg', 'cost' => 300, 'stock' => 15, 'threshold' => 3],
            ['name' => 'Whole Chicken', 'category' => 'meat', 'unit' => 'kg', 'cost' => 220, 'stock' => 30, 'threshold' => 8],
            ['name' => 'Chicken Breast', 'category' => 'meat', 'unit' => 'kg', 'cost' => 260, 'stock' => 20, 'threshold' => 5],
            ['name' => 'Beef Brisket', 'category' => 'meat', 'unit' => 'kg', 'cost' => 420, 'stock' => 15, 'threshold' => 3],
            ['name' => 'Ground Beef', 'category' => 'meat', 'unit' => 'kg', 'cost' => 380, 'stock' => 10, 'threshold' => 3],
            // Seafood
            ['name' => 'Bangus (Milkfish)', 'category' => 'meat', 'unit' => 'kg', 'cost' => 240, 'stock' => 15, 'threshold' => 5],
            ['name' => 'Tilapia', 'category' => 'meat', 'unit' => 'kg', 'cost' => 180, 'stock' => 15, 'threshold' => 5],
            ['name' => 'Shrimp', 'category' => 'meat', 'unit' => 'kg', 'cost' => 600, 'stock' => 5, 'threshold' => 2],
            // Produce
            ['name' => 'Garlic', 'category' => 'produce', 'unit' => 'kg', 'cost' => 120, 'stock' => 10, 'threshold' => 2],
            ['name' => 'Red Onion', 'category' => 'produce', 'unit' => 'kg', 'cost' => 150, 'stock' => 15, 'threshold' => 3],
            ['name' => 'White Onion', 'category' => 'produce', 'unit' => 'kg', 'cost' => 160, 'stock' => 10, 'threshold' => 2],
            ['name' => 'Ginger', 'category' => 'produce', 'unit' => 'kg', 'cost' => 100, 'stock' => 5, 'threshold' => 1],
            ['name' => 'Tomato', 'category' => 'produce', 'unit' => 'kg', 'cost' => 80, 'stock' => 10, 'threshold' => 2],
            ['name' => 'Potato', 'category' => 'produce', 'unit' => 'kg', 'cost' => 90, 'stock' => 20, 'threshold' => 5],
            ['name' => 'Carrots', 'category' => 'produce', 'unit' => 'kg', 'cost' => 80, 'stock' => 15, 'threshold' => 3],
            ['name' => 'Bell Pepper (Red/Green)', 'category' => 'produce', 'unit' => 'kg', 'cost' => 180, 'stock' => 5, 'threshold' => 1],
            ['name' => 'Cabbage', 'category' => 'produce', 'unit' => 'kg', 'cost' => 70, 'stock' => 10, 'threshold' => 2],
            ['name' => 'Kangkong', 'category' => 'produce', 'unit' => 'kg', 'cost' => 50, 'stock' => 5, 'threshold' => 1],
            ['name' => 'Eggplant', 'category' => 'produce', 'unit' => 'kg', 'cost' => 80, 'stock' => 8, 'threshold' => 2],
            ['name' => 'String Beans (Sitaw)', 'category' => 'produce', 'unit' => 'kg', 'cost' => 90, 'stock' => 8, 'threshold' => 2],
            ['name' => 'Okra', 'category' => 'produce', 'unit' => 'kg', 'cost' => 70, 'stock' => 5, 'threshold' => 1],
            ['name' => 'Radish (Labanos)', 'category' => 'produce', 'unit' => 'kg', 'cost' => 60, 'stock' => 5, 'threshold' => 1],
            ['name' => 'Saba Banana', 'category' => 'produce', 'unit' => 'kg', 'cost' => 50, 'stock' => 20, 'threshold' => 5],
            ['name' => 'Calamansi', 'category' => 'produce', 'unit' => 'kg', 'cost' => 80, 'stock' => 5, 'threshold' => 1],
            // Condiments / Sauces
            ['name' => 'Soy Sauce', 'category' => 'condiments', 'unit' => 'ml', 'cost' => 0.05, 'stock' => 5000, 'threshold' => 1000],
            ['name' => 'Vinegar', 'category' => 'condiments', 'unit' => 'ml', 'cost' => 0.04, 'stock' => 5000, 'threshold' => 1000],
            ['name' => 'Fish Sauce (Patis)', 'category' => 'condiments', 'unit' => 'ml', 'cost' => 0.08, 'stock' => 3000, 'threshold' => 500],
            ['name' => 'Oyster Sauce', 'category' => 'condiments', 'unit' => 'ml', 'cost' => 0.20, 'stock' => 2000, 'threshold' => 500],
            ['name' => 'Tomato Sauce', 'category' => 'canned_goods', 'unit' => 'kg', 'cost' => 60, 'stock' => 20, 'threshold' => 5],
            ['name' => 'Banana Ketchup', 'category' => 'condiments', 'unit' => 'kg', 'cost' => 45, 'stock' => 15, 'threshold' => 5],
            ['name' => 'Mayonnaise', 'category' => 'condiments', 'unit' => 'kg', 'cost' => 180, 'stock' => 10, 'threshold' => 2],
            ['name' => 'Shrimp Paste (Bagoong)', 'category' => 'condiments', 'unit' => 'kg', 'cost' => 120, 'stock' => 10, 'threshold' => 2],
            ['name' => 'Liver Spread', 'category' => 'canned_goods', 'unit' => 'kg', 'cost' => 150, 'stock' => 5, 'threshold' => 1],
            // Spices & Others
            ['name' => 'Salt', 'category' => 'spices', 'unit' => 'kg', 'cost' => 25, 'stock' => 20, 'threshold' => 5],
            ['name' => 'Whole Peppercorn', 'category' => 'spices', 'unit' => 'kg', 'cost' => 400, 'stock' => 2, 'threshold' => 0.5],
            ['name' => 'Ground Black Pepper', 'category' => 'spices', 'unit' => 'kg', 'cost' => 450, 'stock' => 2, 'threshold' => 0.5],
            ['name' => 'White Sugar', 'category' => 'sweeteners', 'unit' => 'kg', 'cost' => 70, 'stock' => 25, 'threshold' => 5],
            ['name' => 'Brown Sugar', 'category' => 'sweeteners', 'unit' => 'kg', 'cost' => 65, 'stock' => 25, 'threshold' => 5],
            ['name' => 'Cooking Oil', 'category' => 'oils', 'unit' => 'ml', 'cost' => 0.08, 'stock' => 20000, 'threshold' => 5000],
            ['name' => 'All-Purpose Flour', 'category' => 'baking', 'unit' => 'kg', 'cost' => 50, 'stock' => 20, 'threshold' => 5],
            ['name' => 'Cornstarch', 'category' => 'thickeners', 'unit' => 'kg', 'cost' => 60, 'stock' => 10, 'threshold' => 2],
            ['name' => 'Bay Leaves (Laurel)', 'category' => 'herbs', 'unit' => 'g', 'cost' => 1.5, 'stock' => 500, 'threshold' => 100],
            // Dairy / Eggs
            ['name' => 'Eggs (Large)', 'category' => 'dairy', 'unit' => 'pcs', 'cost' => 10, 'stock' => 500, 'threshold' => 100],
            ['name' => 'Butter', 'category' => 'dairy', 'unit' => 'kg', 'cost' => 350, 'stock' => 5, 'threshold' => 1],
            ['name' => 'Cheddar Cheese', 'category' => 'dairy', 'unit' => 'kg', 'cost' => 300, 'stock' => 5, 'threshold' => 1],
            ['name' => 'Evaporated Milk', 'category' => 'dairy', 'unit' => 'ml', 'cost' => 0.15, 'stock' => 5000, 'threshold' => 1000],
            ['name' => 'Condensed Milk', 'category' => 'dairy', 'unit' => 'ml', 'cost' => 0.20, 'stock' => 5000, 'threshold' => 1000],
            // Grains
            ['name' => 'Jasmine Rice', 'category' => 'grains', 'unit' => 'kg', 'cost' => 55, 'stock' => 100, 'threshold' => 20],
            ['name' => 'Glutinous Rice (Malagkit)', 'category' => 'grains', 'unit' => 'kg', 'cost' => 70, 'stock' => 20, 'threshold' => 5],
            // Others
            ['name' => 'Lumpia Wrapper', 'category' => 'others', 'unit' => 'pcs', 'cost' => 2, 'stock' => 500, 'threshold' => 100],
            ['name' => 'Ice', 'category' => 'others', 'unit' => 'kg', 'cost' => 5, 'stock' => 100, 'threshold' => 20],
        ];

        foreach ($ingredients as $ing) {
            Ingredient::create([
                'name' => $ing['name'],
                'category' => $ing['category'],
                'unit' => $ing['unit'],
                'cost_per_unit' => $ing['cost'],
                'stock' => $ing['stock'],
                'threshold' => $ing['threshold'],
            ]);
        }

        // 3. Seed Products (Pinoy Carenderia Menu) - ~50 Items
        $products = [
            // MEALS (Ulam)
            ['name' => 'Pork Adobo', 'category' => 'meals', 'price' => 70, 'image' => 'products/adobo.jpg'],
            ['name' => 'Pork Sinigang', 'category' => 'meals', 'price' => 70, 'image' => 'products/sinigang.jpg'],
            ['name' => 'Chicken Adobo', 'category' => 'meals', 'price' => 65, 'image' => 'products/chicken_adobo.jpg'],
            ['name' => 'Chicken Tinola', 'category' => 'meals', 'price' => 65, 'image' => 'products/tinola.jpg'],
            ['name' => 'Beef Nilaga', 'category' => 'meals', 'price' => 80, 'image' => 'products/nilaga.jpg'],
            ['name' => 'Beef Caldereta', 'category' => 'meals', 'price' => 85, 'image' => 'products/caldereta.jpg'],
            ['name' => 'Pork Menudo', 'category' => 'meals', 'price' => 70, 'image' => 'products/menudo.jpg'],
            ['name' => 'Chicken Afritada', 'category' => 'meals', 'price' => 65, 'image' => 'products/afritada.jpg'],
            ['name' => 'Bicol Express', 'category' => 'meals', 'price' => 75, 'image' => 'products/bicol_express.jpg'],
            ['name' => 'Pinakbet w/ Lechon', 'category' => 'meals', 'price' => 60, 'image' => 'products/pinakbet.jpg'],
            ['name' => 'Dinuguan', 'category' => 'meals', 'price' => 70, 'image' => 'products/dinuguan.jpg'],
            ['name' => 'Lechon Kawali', 'category' => 'meals', 'price' => 90, 'image' => 'products/lechon_kawali.jpg'],
            ['name' => 'Pork Sisig', 'category' => 'meals', 'price' => 90, 'image' => 'products/sisig.jpg'],
            ['name' => 'Fried Chicken (1pc)', 'category' => 'meals', 'price' => 60, 'image' => 'products/fried_chicken.jpg'],
            ['name' => 'Daing na Bangus', 'category' => 'meals', 'price' => 85, 'image' => 'products/daing.jpg'],
            ['name' => 'Ginataang Tilapia', 'category' => 'meals', 'price' => 75, 'image' => 'products/ginataan_tilapia.jpg'],
            ['name' => 'Pork BBQ (2 sticks)', 'category' => 'meals', 'price' => 50, 'image' => 'products/bbq.jpg'],
            ['name' => 'Bistek Tagalog', 'category' => 'meals', 'price' => 85, 'image' => 'products/bistek.jpg'],
            ['name' => 'Kare-Kare', 'category' => 'meals', 'price' => 95, 'image' => 'products/kare_kare.jpg'],
            ['name' => 'Shanghai (5pcs)', 'category' => 'meals', 'price' => 40, 'image' => 'products/shanghai.jpg'],
            ['name' => 'Plain Rice', 'category' => 'meals', 'price' => 15, 'image' => 'products/rice.jpg'],
            ['name' => 'Garlic Rice', 'category' => 'meals', 'price' => 25, 'image' => 'products/garlic_rice.jpg'],
            // SNACKS (Merienda)
            ['name' => 'Halo-Halo Special', 'category' => 'snacks', 'price' => 85, 'image' => 'products/halo_halo.jpg'],
            ['name' => 'Halo-Halo Regular', 'category' => 'snacks', 'price' => 65, 'image' => 'products/halo_halo_reg.jpg'],
            ['name' => 'Banana Cue (2pcs)', 'category' => 'snacks', 'price' => 20, 'image' => 'products/banana_cue.jpg'],
            ['name' => 'Turon (2pcs)', 'category' => 'snacks', 'price' => 20, 'image' => 'products/turon.jpg'],
            ['name' => 'Camote Cue', 'category' => 'snacks', 'price' => 20, 'image' => 'products/camote_cue.jpg'],
            ['name' => 'Pancit Canton', 'category' => 'snacks', 'price' => 50, 'image' => 'products/pancit.jpg'],
            ['name' => 'Pancit Palabok', 'category' => 'snacks', 'price' => 60, 'image' => 'products/palabok.jpg'],
            ['name' => 'Spaghetti', 'category' => 'snacks', 'price' => 50, 'image' => 'products/spaghetti.jpg'],
            ['name' => 'Burger Steak w/ Rice', 'category' => 'snacks', 'price' => 60, 'image' => 'products/burger_steak.jpg'],
            ['name' => 'Cheeseburger', 'category' => 'snacks', 'price' => 45, 'image' => 'products/burger.jpg'],
            ['name' => 'Fries', 'category' => 'snacks', 'price' => 40, 'image' => 'products/fries.jpg'],
            ['name' => 'Siopao Asado', 'category' => 'snacks', 'price' => 35, 'image' => 'products/siopao.jpg'],
            ['name' => 'Siopao Bola-Bola', 'category' => 'snacks', 'price' => 35, 'image' => 'products/siopao.jpg'],
            ['name' => 'Siomai (4pcs)', 'category' => 'snacks', 'price' => 35, 'image' => 'products/siomai.jpg'],
            ['name' => 'Fishball Cup', 'category' => 'snacks', 'price' => 20, 'image' => 'products/fishball.jpg'],
            ['name' => 'Kikiam Cup', 'category' => 'snacks', 'price' => 25, 'image' => 'products/kikiam.jpg'],
            ['name' => 'Bibingka', 'category' => 'snacks', 'price' => 40, 'image' => 'products/bibingka.jpg'],
            ['name' => 'Puto (3pcs)', 'category' => 'snacks', 'price' => 15, 'image' => 'products/puto.jpg'],
            ['name' => 'Suman', 'category' => 'snacks', 'price' => 15, 'image' => 'products/suman.jpg'],
            ['name' => 'Empanada', 'category' => 'snacks', 'price' => 25, 'image' => 'products/empanada.jpg'],
            // DRINKS (prepared in-house)
            ['name' => 'Buko Juice', 'category' => 'drinks', 'price' => 25, 'image' => 'products/buko.jpg'],
            ['name' => 'Sago\'t Gulaman', 'category' => 'drinks', 'price' => 25, 'image' => 'products/sago_gulaman.jpg'],
            ['name' => 'Melon Juice', 'category' => 'drinks', 'price' => 25, 'image' => 'products/melon.jpg'],
            ['name' => 'Iced Tea House Blend', 'category' => 'drinks', 'price' => 25, 'image' => 'products/iced_tea.jpg'],
            ['name' => 'Pineapple Juice', 'category' => 'drinks', 'price' => 30, 'image' => 'products/pineapple.jpg'],
            ['name' => 'Hot Coffee', 'category' => 'drinks', 'price' => 20, 'image' => 'products/coffee.jpg'],
            ['name' => 'Iced Coffee', 'category' => 'drinks', 'price' => 35, 'image' => 'products/iced_coffee.jpg'],
            // READY MADE (packaged — no recipes, stocked via Product Stock In)
            ['name' => 'Coke Mismo', 'category' => 'ready_made', 'price' => 25, 'image' => 'products/coke.jpg'],
            ['name' => 'Sprite Mismo', 'category' => 'ready_made', 'price' => 25, 'image' => 'products/sprite.jpg'],
            ['name' => 'Royal Mismo', 'category' => 'ready_made', 'price' => 25, 'image' => 'products/royal.jpg'],
            ['name' => 'Coke Can', 'category' => 'ready_made', 'price' => 45, 'image' => 'products/coke_can.jpg'],
            ['name' => 'Bottled Water 500ml', 'category' => 'ready_made', 'price' => 20, 'image' => 'products/water.jpg'],
            ['name' => 'Mang Juan', 'category' => 'ready_made', 'price' => 10, 'image' => 'products/mang_juan.jpg'],
            ['name' => 'Pillows', 'category' => 'ready_made', 'price' => 10, 'image' => 'products/pillows.jpg'],
            ['name' => 'Oishi Prawn Crackers', 'category' => 'ready_made', 'price' => 15, 'image' => 'products/oishi.jpg'],
        ];

        foreach ($products as $prod) {
            $productModel = Product::create([
                'name' => $prod['name'],
                'category' => $prod['category'],
                'price' => $prod['price'],
                'image' => $prod['image'],
            ]);

            // Create batch size for all products
            $servings = ($prod['category'] === 'meals') ? 10 : 5;
            $batchSize = DB::table('batch_sizes')->insertGetId([
                'product_id' => $productModel->id,
                'servings' => $servings,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Create REALISTIC Recipes (hand-mapped per dish)
        // Ready-made items (sodas, water) get NO recipes — they use Product Stock In
        $readyMade = ['Coke Mismo', 'Sprite Mismo', 'Royal Mismo', 'Coke Can', 'Bottled Water 500ml'];

        $recipeMap = [
            // === MEALS ===
            'Pork Adobo' => [
                'Pork Liempo' => 0.20, 'Soy Sauce' => 30, 'Vinegar' => 20,
                'Garlic' => 0.01, 'Bay Leaves (Laurel)' => 3, 'Whole Peppercorn' => 0.005,
                'Cooking Oil' => 15, 'White Sugar' => 0.01,
            ],
            'Chicken Adobo' => [
                'Whole Chicken' => 0.25, 'Soy Sauce' => 30, 'Vinegar' => 20,
                'Garlic' => 0.01, 'Bay Leaves (Laurel)' => 3, 'Whole Peppercorn' => 0.005,
                'Cooking Oil' => 15,
            ],
            'Pork Sinigang' => [
                'Pork Kasim' => 0.20, 'Tomato' => 0.05, 'Red Onion' => 0.03,
                'Kangkong' => 0.05, 'Radish (Labanos)' => 0.05,
                'String Beans (Sitaw)' => 0.03, 'Fish Sauce (Patis)' => 15,
            ],
            'Chicken Tinola' => [
                'Whole Chicken' => 0.25, 'Ginger' => 0.02, 'Garlic' => 0.01,
                'Red Onion' => 0.03, 'Fish Sauce (Patis)' => 10, 'Kangkong' => 0.05,
            ],
            'Beef Nilaga' => [
                'Beef Brisket' => 0.20, 'Potato' => 0.10, 'Cabbage' => 0.05,
                'Red Onion' => 0.03, 'Whole Peppercorn' => 0.003, 'Salt' => 0.005,
            ],
            'Beef Caldereta' => [
                'Beef Brisket' => 0.20, 'Potato' => 0.08, 'Carrots' => 0.05,
                'Bell Pepper (Red/Green)' => 0.03, 'Tomato Sauce' => 0.05,
                'Liver Spread' => 0.02, 'Red Onion' => 0.03, 'Garlic' => 0.01,
                'Cooking Oil' => 15,
            ],
            'Pork Menudo' => [
                'Pork Kasim' => 0.15, 'Potato' => 0.05, 'Carrots' => 0.05,
                'Tomato Sauce' => 0.04, 'Soy Sauce' => 10, 'Red Onion' => 0.02,
                'Garlic' => 0.01, 'Cooking Oil' => 15,
            ],
            'Chicken Afritada' => [
                'Whole Chicken' => 0.25, 'Potato' => 0.08, 'Carrots' => 0.05,
                'Bell Pepper (Red/Green)' => 0.03, 'Tomato Sauce' => 0.05,
                'Red Onion' => 0.03, 'Garlic' => 0.01, 'Cooking Oil' => 15,
            ],
            'Bicol Express' => [
                'Pork Liempo' => 0.20, 'Shrimp Paste (Bagoong)' => 0.02,
                'Red Onion' => 0.02, 'Garlic' => 0.01, 'Cooking Oil' => 15,
            ],
            'Pinakbet w/ Lechon' => [
                'Pork Liempo' => 0.10, 'Eggplant' => 0.05, 'Okra' => 0.05,
                'String Beans (Sitaw)' => 0.05, 'Tomato' => 0.03,
                'Shrimp Paste (Bagoong)' => 0.02, 'Red Onion' => 0.02, 'Garlic' => 0.01,
            ],
            'Dinuguan' => [
                'Pork Kasim' => 0.20, 'Vinegar' => 30, 'Garlic' => 0.01,
                'Red Onion' => 0.03, 'Salt' => 0.005,
            ],
            'Lechon Kawali' => [
                'Pork Liempo' => 0.30, 'Salt' => 0.01, 'Whole Peppercorn' => 0.005,
                'Bay Leaves (Laurel)' => 3, 'Cooking Oil' => 200,
            ],
            'Pork Sisig' => [
                'Pork Liempo' => 0.25, 'Red Onion' => 0.03, 'Calamansi' => 0.02,
                'Mayonnaise' => 0.02, 'Eggs (Large)' => 1, 'Butter' => 0.01,
                'Salt' => 0.005, 'Ground Black Pepper' => 0.003,
            ],
            'Fried Chicken (1pc)' => [
                'Whole Chicken' => 0.20, 'All-Purpose Flour' => 0.05,
                'Cornstarch' => 0.02, 'Salt' => 0.005, 'Ground Black Pepper' => 0.003,
                'Garlic' => 0.005, 'Cooking Oil' => 150,
            ],
            'Daing na Bangus' => [
                'Bangus (Milkfish)' => 0.25, 'Vinegar' => 30, 'Garlic' => 0.015,
                'Salt' => 0.005, 'Whole Peppercorn' => 0.003, 'Cooking Oil' => 50,
            ],
            'Ginataang Tilapia' => [
                'Tilapia' => 0.25, 'Red Onion' => 0.02, 'Garlic' => 0.01,
                'Ginger' => 0.01, 'Salt' => 0.005,
            ],
            'Pork BBQ (2 sticks)' => [
                'Pork Liempo' => 0.15, 'Soy Sauce' => 20, 'Banana Ketchup' => 0.03,
                'White Sugar' => 0.02, 'Garlic' => 0.005, 'Calamansi' => 0.01,
            ],
            'Bistek Tagalog' => [
                'Beef Brisket' => 0.20, 'Soy Sauce' => 30, 'Calamansi' => 0.03,
                'Red Onion' => 0.05, 'Cooking Oil' => 15, 'Ground Black Pepper' => 0.003,
            ],
            'Kare-Kare' => [
                'Beef Brisket' => 0.25, 'Eggplant' => 0.05, 'String Beans (Sitaw)' => 0.05,
                'Kangkong' => 0.05, 'Red Onion' => 0.03, 'Garlic' => 0.01,
                'Cooking Oil' => 15, 'Shrimp Paste (Bagoong)' => 0.03,
            ],
            'Shanghai (5pcs)' => [
                'Ground Pork' => 0.10, 'Carrots' => 0.02, 'Red Onion' => 0.01,
                'Garlic' => 0.005, 'Lumpia Wrapper' => 5, 'Eggs (Large)' => 0.5,
                'Cooking Oil' => 100, 'Salt' => 0.003,
            ],
            'Plain Rice' => [
                'Jasmine Rice' => 0.15,
            ],
            'Garlic Rice' => [
                'Jasmine Rice' => 0.15, 'Garlic' => 0.01, 'Cooking Oil' => 10, 'Salt' => 0.002,
            ],

            // === SNACKS ===
            'Banana Cue (2pcs)' => [
                'Saba Banana' => 0.15, 'Brown Sugar' => 0.04, 'Cooking Oil' => 100,
            ],
            'Turon (2pcs)' => [
                'Saba Banana' => 0.12, 'Lumpia Wrapper' => 2, 'Brown Sugar' => 0.03,
                'Cooking Oil' => 80,
            ],
            'Camote Cue' => [
                'Brown Sugar' => 0.04, 'Cooking Oil' => 100,
            ],
            'Pancit Canton' => [
                'Chicken Breast' => 0.08, 'Cabbage' => 0.05, 'Carrots' => 0.03,
                'Soy Sauce' => 15, 'Oyster Sauce' => 10, 'Garlic' => 0.01,
                'Red Onion' => 0.02, 'Cooking Oil' => 15,
            ],
            'Pancit Palabok' => [
                'Shrimp' => 0.05, 'Ground Pork' => 0.05, 'Eggs (Large)' => 1,
                'Garlic' => 0.01, 'Fish Sauce (Patis)' => 10, 'Calamansi' => 0.02,
            ],
            'Spaghetti' => [
                'Ground Pork' => 0.08, 'Tomato Sauce' => 0.08, 'Banana Ketchup' => 0.03,
                'White Sugar' => 0.02, 'Red Onion' => 0.02, 'Garlic' => 0.01,
                'Cheddar Cheese' => 0.02, 'Cooking Oil' => 10,
            ],
            'Burger Steak w/ Rice' => [
                'Ground Beef' => 0.12, 'Red Onion' => 0.03, 'Eggs (Large)' => 0.5,
                'All-Purpose Flour' => 0.02, 'Soy Sauce' => 10, 'Butter' => 0.01,
                'Jasmine Rice' => 0.15,
            ],
            'Cheeseburger' => [
                'Ground Beef' => 0.10, 'Cheddar Cheese' => 0.02, 'Red Onion' => 0.02,
                'Tomato' => 0.02, 'Mayonnaise' => 0.01,
            ],
            'Fries' => [
                'Potato' => 0.15, 'Cooking Oil' => 150, 'Salt' => 0.005,
            ],
            'Siomai (4pcs)' => [
                'Ground Pork' => 0.08, 'Carrots' => 0.02, 'Red Onion' => 0.01,
                'Soy Sauce' => 5, 'Salt' => 0.003,
            ],
            'Bibingka' => [
                'Glutinous Rice (Malagkit)' => 0.08, 'Eggs (Large)' => 1,
                'White Sugar' => 0.03, 'Butter' => 0.02, 'Cheddar Cheese' => 0.02,
            ],
            'Puto (3pcs)' => [
                'All-Purpose Flour' => 0.08, 'White Sugar' => 0.04,
                'Eggs (Large)' => 1, 'Cheddar Cheese' => 0.01,
            ],
            'Suman' => [
                'Glutinous Rice (Malagkit)' => 0.10, 'White Sugar' => 0.02,
            ],
            'Empanada' => [
                'All-Purpose Flour' => 0.06, 'Ground Pork' => 0.05,
                'Potato' => 0.03, 'Carrots' => 0.02, 'Red Onion' => 0.01,
                'Eggs (Large)' => 0.5, 'Cooking Oil' => 80,
            ],

            // === DRINKS (prepared in-house) ===
            'Buko Juice' => [
                'White Sugar' => 0.02, 'Ice' => 0.10,
            ],
            'Sago\'t Gulaman' => [
                'Brown Sugar' => 0.03, 'Ice' => 0.10,
            ],
            'Melon Juice' => [
                'White Sugar' => 0.02, 'Ice' => 0.10, 'Evaporated Milk' => 30,
            ],
            'Iced Tea House Blend' => [
                'White Sugar' => 0.02, 'Ice' => 0.10,
            ],
            'Pineapple Juice' => [
                'White Sugar' => 0.02, 'Ice' => 0.10,
            ],
            'Hot Coffee' => [
                'White Sugar' => 0.01, 'Evaporated Milk' => 20,
            ],
            'Iced Coffee' => [
                'White Sugar' => 0.02, 'Ice' => 0.10, 'Evaporated Milk' => 30,
            ],
            // Halo-Halo
            'Halo-Halo Special' => [
                'Evaporated Milk' => 50, 'White Sugar' => 0.03, 'Ice' => 0.20,
            ],
            'Halo-Halo Regular' => [
                'Evaporated Milk' => 30, 'White Sugar' => 0.02, 'Ice' => 0.15,
            ],
        ];

        // Create ingredient lookup by name
        $ingredientLookup = Ingredient::pluck('id', 'name');

        foreach ($recipeMap as $productName => $ingredients) {
            $product = Product::where('name', $productName)->first();
            if (!$product) continue;

            $batchSizeId = DB::table('batch_sizes')->where('product_id', $product->id)->value('id');
            if (!$batchSizeId) continue;

            foreach ($ingredients as $ingredientName => $qty) {
                $ingredientId = $ingredientLookup[$ingredientName] ?? null;
                if (!$ingredientId) continue;

                Recipe::create([
                    'product_id' => $product->id,
                    'batch_sizes_id' => $batchSizeId,
                    'ingredient_id' => $ingredientId,
                    'quantity' => $qty,
                ]);
            }
        }
    }
}
