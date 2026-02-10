<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up()
    {
        Schema::create('recipe_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade');
            $table->decimal('old_quantity', 10, 2)->nullable();
            $table->decimal('new_quantity', 10, 2)->nullable();
            $table->string('action');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('recipe_audit_logs');
    }
};