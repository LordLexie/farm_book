<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livestock_id')->nullable()->constrained('farm_livestocks')->nullOnDelete();
            $table->foreignId('farm_session_id')->nullable()->constrained('farm_sessions')->nullOnDelete();
            $table->foreignId('farm_item_id')->constrained('farm_items');
            $table->decimal('quantity', 15, 3);
            $table->date('consumption_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_consumptions');
    }
};
