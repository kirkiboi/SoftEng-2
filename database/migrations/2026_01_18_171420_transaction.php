<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->decimal('total_amount', 10, 2);
            $table->string('pay_method');
            $table->decimal('trans_discount', 10, 2);
            $table->decimal('net_amount', 10, 2);
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
    }
};
