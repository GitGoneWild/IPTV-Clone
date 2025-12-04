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
        Schema::create('geo_restrictions', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2);
            $table->enum('type', ['allow', 'block'])->default('block');
            $table->string('restrictable_type')->nullable();
            $table->unsignedBigInteger('restrictable_id')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['country_code', 'type']);
            $table->index(['restrictable_type', 'restrictable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo_restrictions');
    }
};
