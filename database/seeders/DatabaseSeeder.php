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
        try {
            // Disable foreign key checks to allow truncation
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Truncate tables to start fresh
            DB::table('users')->truncate();
            DB::table('products')->truncate();
            DB::table('ingredients')->truncate();
            DB::table('batch_sizes')->truncate();
            DB::table('recipes')->truncate();
            DB::table('transaction_items')->truncate();
            DB::table('transactions')->truncate();
            DB::table('product_audit_logs')->truncate();
            DB::table('ingredient_audit_logs')->truncate();
            DB::table('kitchen_production_logs')->truncate();
            DB::table('kitchen_stock_deductions')->truncate();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // 1. Create Default User
            User::factory()->create([
                'role' => 'admin',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
        } catch (\Exception $e) {
            $this->command->error($e->getMessage());
            throw $e;
        }
    }
}
