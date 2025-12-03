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
            // is_active and expires_at single indexes already exist in create_users_table migration
            $table->index(['is_active', 'expires_at']);
        });

        Schema::table('streams', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('server_id');
            $table->index(['is_active', 'is_hidden']);
            // epg_channel_id index already exists in create_streams_table migration
            $table->index('sort_order');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('parent_id');
            // is_active index already exists in create_categories_table migration
            $table->index('sort_order');
        });

        Schema::table('epg_programs', function (Blueprint $table) {
            // channel_id and ['start_time', 'end_time'] indexes already exist in create_epg_programs_table migration
            $table->index(['channel_id', 'start_time']);
            $table->index(['channel_id', 'end_time']);
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
            // user_id, created_at, and ['user_id', 'created_at'] indexes already exist in create_api_usage_logs_table migration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['username']);
            // is_active and expires_at single indexes are managed by create_users_table migration
            $table->dropIndex(['is_active', 'expires_at']);
        });

        Schema::table('streams', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['server_id']);
            $table->dropIndex(['is_active', 'is_hidden']);
            // epg_channel_id index is managed by create_streams_table migration
            $table->dropIndex(['sort_order']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
            // is_active index is managed by create_categories_table migration
            $table->dropIndex(['sort_order']);
        });

        Schema::table('epg_programs', function (Blueprint $table) {
            // channel_id and ['start_time', 'end_time'] indexes are managed by create_epg_programs_table migration
            $table->dropIndex(['channel_id', 'start_time']);
            $table->dropIndex(['channel_id', 'end_time']);
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
            // user_id, created_at, and ['user_id', 'created_at'] indexes are managed by create_api_usage_logs_table migration
        });
    }
};
