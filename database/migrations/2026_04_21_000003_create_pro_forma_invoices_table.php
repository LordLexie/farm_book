<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pro_forma_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('status_id')->nullable()->constrained('statuses');
            $table->foreignId('currency_id')->constrained('currencies');
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->date('date');
            $table->decimal('discount', 5, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_forma_invoices');
    }
};
