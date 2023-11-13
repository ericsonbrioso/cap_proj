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
        Schema::create('rent_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rent_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('quantity')->nullable();
            $table->decimal('unit_price', 10 ,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_packages');
    }
};
