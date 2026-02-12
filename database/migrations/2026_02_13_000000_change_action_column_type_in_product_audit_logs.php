<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change enum to string to allow dynamic waste reasons
        DB::statement("ALTER TABLE product_audit_logs MODIFY COLUMN action VARCHAR(255)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         // Reverting to enum might be tricky if data doesn't fit, so we keep it as string or try best effort
         // DB::statement("ALTER TABLE product_audit_logs MODIFY COLUMN action ENUM('added', 'edited', 'deleted')");
    }
};
