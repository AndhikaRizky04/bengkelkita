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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('vehicle_brand_id')->constrained('vehicle_brands')->onDelete('cascade');
            $table->foreignId('vehicle_model_id')->constrained('vehicle_models')->onDelete('cascade');
            $table->string('license_plate')->index();
            $table->smallInteger('year')->nullable();
            $table->string('color')->nullable();
            $table->integer('last_kilometer')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
