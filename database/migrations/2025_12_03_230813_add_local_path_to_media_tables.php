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
        // Add local path and download tracking to movies
        Schema::table('movies', function (Blueprint $table) {
            $table->string('local_path')->nullable()->after('stream_url');
            $table->enum('download_status', ['pending', 'downloading', 'completed', 'failed'])->nullable()->after('local_path');
            $table->unsignedInteger('download_progress')->default(0)->after('download_status');
            $table->text('download_error')->nullable()->after('download_progress');
            $table->timestamp('downloaded_at')->nullable()->after('download_error');
        });

        // Add local path and download tracking to episodes
        Schema::table('episodes', function (Blueprint $table) {
            $table->string('local_path')->nullable()->after('stream_url');
            $table->enum('download_status', ['pending', 'downloading', 'completed', 'failed'])->nullable()->after('local_path');
            $table->unsignedInteger('download_progress')->default(0)->after('download_status');
            $table->text('download_error')->nullable()->after('download_progress');
            $table->timestamp('downloaded_at')->nullable()->after('download_error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['local_path', 'download_status', 'download_progress', 'download_error', 'downloaded_at']);
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->dropColumn(['local_path', 'download_status', 'download_progress', 'download_error', 'downloaded_at']);
        });
    }
};
