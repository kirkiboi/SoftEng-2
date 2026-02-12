<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up()
    {
        Schema::create('kitchen_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('batch_size'); // 10, 20, 30, 40, 50
            $table->integer('times_cooked');
            $table->timestamp('cooked_at');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('kitchen_logs');
    }
};