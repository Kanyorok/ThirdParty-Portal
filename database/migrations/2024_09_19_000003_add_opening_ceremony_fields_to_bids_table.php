<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('t_BidSubmissions')) {
            return;
        }
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            // Opening ceremony specific fields
            $table->enum('CeremonyType', ['public', 'recorded', 'private'])->nullable()->after('OpenedBy');
            $table->text('CeremonyNotes')->nullable()->after('CeremonyType');
            $table->text('OfficersPresent')->nullable()->after('CeremonyNotes');
            $table->text('ReadOutSummary')->nullable()->after('OfficersPresent'); // Public read-out details
            $table->boolean('BidSecurityPresent')->nullable()->after('ReadOutSummary');
            $table->boolean('ReceivedOnTime')->nullable()->after('BidSecurityPresent');
            
            // Add indexes for ceremony tracking
            $table->index(['OpenedAt', 'CeremonyType']);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('t_BidSubmissions')) {
            return;
        }
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            $table->dropIndex(['OpenedAt', 'CeremonyType']);
            $table->dropColumn([
                'ReceivedOnTime',
                'BidSecurityPresent',
                'ReadOutSummary',
                'OfficersPresent',
                'CeremonyNotes',
                'CeremonyType'
            ]);
        });
    }
};
