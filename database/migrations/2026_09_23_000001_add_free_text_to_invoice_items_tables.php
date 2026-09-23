<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('name')->nullable()->after('invoiceable_id');
            $table->text('description')->nullable()->after('name');
            $table->string('invoiceable_type', 50)->nullable()->change();
            $table->unsignedBigInteger('invoiceable_id')->nullable()->change();
        });

        Schema::table('pro_forma_invoice_items', function (Blueprint $table) {
            $table->string('name')->nullable()->after('invoiceable_id');
            $table->text('description')->nullable()->after('name');
            $table->string('invoiceable_type')->nullable()->change();
            $table->unsignedBigInteger('invoiceable_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
            $table->string('invoiceable_type', 50)->nullable(false)->change();
            $table->unsignedBigInteger('invoiceable_id')->nullable(false)->change();
        });

        Schema::table('pro_forma_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
            $table->string('invoiceable_type')->nullable(false)->change();
            $table->unsignedBigInteger('invoiceable_id')->nullable(false)->change();
        });
    }
};
