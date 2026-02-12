<?php
use App\Models\Product;
use App\Models\BatchSize;
use App\Models\Ingredient;

echo "Products: " . Product::count() . "\n";
echo "BatchSizes: " . BatchSize::count() . "\n";
echo "Ingredients: " . Ingredient::count() . "\n";
