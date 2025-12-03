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
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('stream_url');
            $table->string('stream_type')->default('hls');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->string('epg_channel_id')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('stream_icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_hidden')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('custom_sid')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_check_at')->nullable();
            $table->string('last_check_status')->nullable();
            $table->string('bitrate')->nullable();
            $table->string('resolution')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('epg_channel_id');
            $table->index(['category_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('streams');
    }
};
