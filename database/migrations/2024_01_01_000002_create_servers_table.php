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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('base_url');
            $table->string('rtmp_url')->nullable();
            $table->integer('http_port')->default(80);
            $table->integer('https_port')->default(443);
            $table->integer('rtmp_port')->default(1935);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_primary')->default(false);
            $table->integer('weight')->default(1);
            $table->integer('max_connections')->nullable();
            $table->integer('current_connections')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('last_check_at')->nullable();
            $table->string('last_check_status')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
