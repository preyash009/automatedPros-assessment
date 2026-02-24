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
        Schema::table('events', function (Blueprint $table) {
            $table->index('location');
            $table->index('date');
            $table->index('title');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['user_id', 'ticket_id', 'status'], 'idx_user_ticket_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_user_ticket_status');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['date']);
            $table->dropIndex(['location']);
        });
    }
};
