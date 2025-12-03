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
        Schema::table('users', function (Blueprint $table) {
            $table->index('username');
            $table->index('is_active');
            $table->index('expires_at');
            $table->index(['is_active', 'expires_at']);
        });

        Schema::table('streams', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('server_id');
            $table->index(['is_active', 'is_hidden']);
            $table->index('epg_channel_id');
            $table->index('sort_order');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('parent_id');
            $table->index('is_active');
            $table->index('sort_order');
        });

        Schema::table('epg_programs', function (Blueprint $table) {
            $table->index('channel_id');
            $table->index(['channel_id', 'start_time']);
            $table->index(['channel_id', 'end_time']);
            $table->index(['start_time', 'end_time']);
        });

        Schema::table('bouquet_streams', function (Blueprint $table) {
            $table->index('bouquet_id');
            $table->index('stream_id');
        });

        Schema::table('user_bouquets', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('bouquet_id');
        });

        Schema::table('connection_logs', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::table('api_usage_logs', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['username']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['expires_at']);
            $table->dropIndex(['is_active', 'expires_at']);
        });

        Schema::table('streams', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['server_id']);
            $table->dropIndex(['is_active', 'is_hidden']);
            $table->dropIndex(['epg_channel_id']);
            $table->dropIndex(['sort_order']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['sort_order']);
        });

        Schema::table('epg_programs', function (Blueprint $table) {
            $table->dropIndex(['channel_id']);
            $table->dropIndex(['channel_id', 'start_time']);
            $table->dropIndex(['channel_id', 'end_time']);
            $table->dropIndex(['start_time', 'end_time']);
        });

        Schema::table('bouquet_streams', function (Blueprint $table) {
            $table->dropIndex(['bouquet_id']);
            $table->dropIndex(['stream_id']);
        });

        Schema::table('user_bouquets', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['bouquet_id']);
        });

        Schema::table('connection_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('api_usage_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id', 'created_at']);
        });
    }
};
