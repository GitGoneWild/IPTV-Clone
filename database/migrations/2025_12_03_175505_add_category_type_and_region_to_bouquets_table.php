<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bouquets', function (Blueprint $table) {
            $table->string('category_type')->default('live_tv')->after('name');
            $table->string('region')->nullable()->after('category_type');
            
            $table->index('category_type');
            $table->index('region');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bouquets', function (Blueprint $table) {
            $table->dropIndex(['category_type']);
            $table->dropIndex(['region']);
            $table->dropColumn(['category_type', 'region']);
        });
    }
};
