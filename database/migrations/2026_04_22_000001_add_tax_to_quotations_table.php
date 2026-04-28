<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('currency_id')->nullable()->after('status_id')->constrained('currencies')->nullOnDelete();
            $table->foreignId('tax_id')->nullable()->after('currency_id')->constrained('taxes')->nullOnDelete();
            $table->decimal('discount', 5, 2)->default(0)->after('tax_id');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['tax_id']);
            $table->dropColumn(['currency_id', 'tax_id', 'discount']);
        });
    }
};
