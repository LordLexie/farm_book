<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Delivery notes are documents only: receipt is filled in by hand on the printout,
// so the dispatch/delivery tracking columns are no longer needed.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('delivery_notes')->where('status', '!=', 'cancelled')->update(['status' => 'active']);

        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->change(); // active | cancelled
            $table->dropColumn(['dispatched_at', 'delivered_at', 'received_by']);
        });

        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->dropColumn('qty_delivered');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->decimal('qty_delivered', 15, 4)->nullable()->after('qty_ordered');
        });

        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
            $table->dateTime('dispatched_at')->nullable()->after('status');
            $table->dateTime('delivered_at')->nullable()->after('dispatched_at');
            $table->string('received_by')->nullable()->after('delivered_at');
        });

        DB::table('delivery_notes')->where('status', 'active')->update(['status' => 'pending']);
    }
};
