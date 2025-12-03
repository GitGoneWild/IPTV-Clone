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
        Schema::create('load_balancers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hostname');
            $table->string('ip_address');
            $table->integer('port')->default(80);
            $table->string('api_key')->unique();
            $table->boolean('is_active')->default(true);
            $table->integer('weight')->default(1)->comment('Weight for load balancing algorithm');
            $table->integer('max_connections')->nullable()->comment('Maximum concurrent connections');
            $table->integer('current_connections')->default(0);
            $table->string('region')->nullable()->comment('Geographic region (e.g., US-East, EU-West)');
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('offline');
            $table->text('capabilities')->nullable()->comment('JSON array of supported features');
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamp('last_check_at')->nullable();
            $table->string('last_check_status')->nullable();
            $table->integer('response_time_ms')->nullable()->comment('Average response time in milliseconds');
            $table->decimal('cpu_usage', 5, 2)->nullable()->comment('CPU usage percentage');
            $table->decimal('memory_usage', 5, 2)->nullable()->comment('Memory usage percentage');
            $table->bigInteger('bandwidth_in')->default(0)->comment('Incoming bandwidth in bytes');
            $table->bigInteger('bandwidth_out')->default(0)->comment('Outgoing bandwidth in bytes');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('status');
            $table->index('region');
            $table->index('last_heartbeat_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('load_balancers');
    }
};
