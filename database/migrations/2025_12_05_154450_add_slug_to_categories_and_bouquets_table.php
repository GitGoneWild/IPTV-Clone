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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        Schema::table('bouquets', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });
        
        // Generate slugs for existing records
        $categories = \DB::table('categories')->whereNull('slug')->get();
        foreach ($categories as $category) {
            $slug = \Illuminate\Support\Str::slug($category->name);
            $count = 1;
            $originalSlug = $slug;
            
            // Ensure uniqueness
            while (\DB::table('categories')->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            \DB::table('categories')->where('id', $category->id)->update(['slug' => $slug]);
        }
        
        $bouquets = \DB::table('bouquets')->whereNull('slug')->get();
        foreach ($bouquets as $bouquet) {
            $slug = \Illuminate\Support\Str::slug($bouquet->name);
            $count = 1;
            $originalSlug = $slug;
            
            // Ensure uniqueness
            while (\DB::table('bouquets')->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            \DB::table('bouquets')->where('id', $bouquet->id)->update(['slug' => $slug]);
        }
        
        // Now make the columns unique and non-nullable
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable(false)->change();
        });

        Schema::table('bouquets', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('bouquets', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
