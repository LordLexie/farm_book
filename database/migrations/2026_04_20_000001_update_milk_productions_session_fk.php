<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('milk_productions', function (Blueprint $table) {
            $table->dropForeign(['farm_session_id']);
            $table->foreign('farm_session_id')->references('id')->on('farm_session_templates')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('milk_productions', function (Blueprint $table) {
            $table->dropForeign(['farm_session_id']);
            $table->foreign('farm_session_id')->references('id')->on('farm_sessions')->cascadeOnDelete();
        });
    }
};
