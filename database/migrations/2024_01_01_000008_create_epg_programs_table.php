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
        Schema::create('epg_programs', function (Blueprint $table) {
            $table->id();
            $table->string('channel_id')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->string('category')->nullable();
            $table->string('episode_num')->nullable();
            $table->string('icon_url')->nullable();
            $table->string('lang')->default('en');
            $table->timestamps();

            $table->index(['channel_id', 'start_time', 'end_time']);
            $table->index(['start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('epg_programs');
    }
};
