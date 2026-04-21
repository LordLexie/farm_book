<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pro_forma_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pro_forma_invoice_id')->constrained('pro_forma_invoices')->cascadeOnDelete();
            $table->string('invoiceable_type');
            $table->unsignedBigInteger('invoiceable_id');
            $table->foreignId('unit_of_measure_id')->constrained('unit_of_measures');
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_forma_invoice_items');
    }
};
