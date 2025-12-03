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
        Schema::create('epg_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('url')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_import_at')->nullable();
            $table->string('last_import_status')->nullable();
            $table->integer('programs_count')->default(0);
            $table->integer('channels_count')->default(0);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('epg_sources');
    }
};
