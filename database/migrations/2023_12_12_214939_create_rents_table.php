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
            $table->integer('quantity');
            $table->decimal('unit_price', 10 ,2)->nullable();
            $table->decimal('total_price', 10 ,2)->nullable();
            $table->string('address');
            $table->string('contact');
            $table->dateTime('date_of_pickup');
            $table->dateTime('date_of_delivery')->nullable();
            $table->enum('status',['pending','approved','rejected','cancelled'])->default('pending');
            $table->softDeletes();
            $table->timestamps();
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
