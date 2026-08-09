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
        Schema::create('service_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('service_order_id')->nullable()->constrained('service_orders')->onDelete('set null');
            $table->foreignId('wash_order_id')->nullable()->constrained('wash_orders')->onDelete('set null');
            $table->date('service_date');
            $table->integer('kilometer')->nullable();
            $table->text('description');
            $table->text('services_performed')->nullable();
            $table->text('spareparts_used')->nullable();
            $table->text('oil_used')->nullable();
            $table->string('mechanic_name')->nullable();
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_histories');
    }
};
