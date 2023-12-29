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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('rent_number')->unique();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10 ,2)->nullable();
            $table->decimal('total_price', 10 ,2)->nullable();
            $table->decimal('delivery_fee', 10 ,2);
            $table->string('address');
            $table->string('contact');
            $table->string('type');
            $table->dateTime('return');
            $table->dateTime('delivery');
            $table->string('status')->default('checkout');
            $table->integer('rating')->nullable();
            $table->string('comment')->nullable();
            $table->string('image')->nullable();
            $table->softDeletes();
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
