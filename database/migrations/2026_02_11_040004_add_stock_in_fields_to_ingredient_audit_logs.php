<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredient_audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('ingredient_audit_logs', 'quantity_changed')) {
                $table->decimal('quantity_changed', 10, 2)->nullable()->after('action');
            }
            if (!Schema::hasColumn('ingredient_audit_logs', 'old_stock')) {
                $table->decimal('old_stock', 10, 2)->nullable()->after('quantity_changed');
            }
            if (!Schema::hasColumn('ingredient_audit_logs', 'new_stock')) {
                $table->decimal('new_stock', 10, 2)->nullable()->after('old_stock');
            }
            if (!Schema::hasColumn('ingredient_audit_logs', 'supplier')) {
                $table->string('supplier')->nullable()->after('new_stock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ingredient_audit_logs', function (Blueprint $table) {
            $table->dropColumn(['quantity_changed', 'old_stock', 'new_stock', 'supplier']);
        });
    }
};
