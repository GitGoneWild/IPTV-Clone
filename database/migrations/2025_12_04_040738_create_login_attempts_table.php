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
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('username')->nullable();
            $table->boolean('successful')->default(false);
            $table->text('user_agent')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('city')->nullable();
            $table->timestamps();

            $table->index(['ip_address', 'created_at']);
            $table->index(['username', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
