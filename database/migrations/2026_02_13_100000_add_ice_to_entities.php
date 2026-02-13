<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add ICE to customers
        Schema::table('customers', function (Blueprint $table) {
            $table->string('ice', 50)->nullable()->after('address');
        });

        // Add ICE to suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('ice', 50)->nullable()->after('address');
        });

        // Add business identifiers to settings
        Schema::table('settings', function (Blueprint $table) {
            $table->string('ice', 50)->nullable();
            $table->string('tax_number')->nullable();
            $table->string('rc')->nullable();
            $table->string('if')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['if', 'rc', 'tax_number', 'ice']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('ice');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('ice');
        });
    }
};
