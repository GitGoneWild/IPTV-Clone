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
        Schema::table('load_balancers', function (Blueprint $table) {
            $table->boolean('use_ssl')->default(false)->after('port');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('load_balancers', function (Blueprint $table) {
            $table->dropColumn('use_ssl');
        });
    }
};
