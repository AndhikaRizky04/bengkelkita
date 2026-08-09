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
        Schema::create('wash_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->nullable()->constrained('queues')->onDelete('set null');
            $table->foreignId('service_order_id')->nullable()->constrained('service_orders')->onDelete('set null');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('wash_package_id')->constrained('wash_packages')->onDelete('cascade');
            $table->foreignId('washer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('menunggu');
            $table->decimal('price', 12, 2);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wash_orders');
    }
};
