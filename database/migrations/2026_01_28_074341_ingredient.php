<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->enum('category', ['meat','produce','dairy','grains','canned_goods','sweeteners','spices','oils','baking','herbs','acids','liquids','thickeners','condiments','others']);
        $table->enum('unit', ['kg','g','ml','pcs']);
        $table->decimal('cost_per_unit', 10,2)->default(0);
        $table->decimal('stock', 10,2)->default(0);
        $table->decimal('threshold', 10,2)->default(0);
        $table->timestamps();
        });
    }
    public function down(): void
    {
    }
};