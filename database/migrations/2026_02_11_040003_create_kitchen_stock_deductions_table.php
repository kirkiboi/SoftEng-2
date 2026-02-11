<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitchen_stock_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_production_log_id')->constrained('kitchen_production_logs')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ingredient_name');
            $table->decimal('quantity_deducted', 10, 2);
            $table->string('unit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_stock_deductions');
    }
};
