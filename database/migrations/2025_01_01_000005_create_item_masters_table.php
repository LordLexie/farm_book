<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_masters', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('item_category_id')->constrained('item_categories');
            $table->foreignId('unit_of_measure_id')->constrained('unit_of_measures');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_masters');
    }
};
