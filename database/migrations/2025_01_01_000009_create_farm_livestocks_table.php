<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_livestocks', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->foreignId('farm_id')->constrained('farms');
            $table->foreignId('livestock_type_id')->constrained('livestock_types');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('breed', 100)->nullable();
            $table->foreignId('status_id')->constrained('statuses');
            $table->foreignId('gender_id')->constrained('genders');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_livestocks');
    }
};
