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
        Schema::create('rents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('rent_number')->unique();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10 ,2)->nullable();
            $table->decimal('total_price', 10 ,2)->nullable();
            $table->decimal('delivery_fee', 10 ,2);
            $table->string('address');
            $table->string('contact');
            $table->string('type');
            $table->dateTime('date_of_pickup');
            $table->dateTime('date_of_delivery')->nullable();
            $table->string('status')->default('checkout');
            $table->softDeletes();
            $table->timestamps();
            $table->integer('rating')->nullable();
            $table->string('comment')->nullable();
            $table->string('image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rents');
    }
};
