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

        // Opening ceremony fields
        if (!Schema::hasColumn('t_BidSubmissions', 'CeremonyType')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                // Use string for SQL Server compatibility instead of enum
                $table->string('CeremonyType', 20)->nullable()->after('OpenedBy');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'CeremonyNotes')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->text('CeremonyNotes')->nullable()->after('CeremonyType');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'OfficersPresent')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->text('OfficersPresent')->nullable()->after('CeremonyNotes');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'ReadOutSummary')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->text('ReadOutSummary')->nullable()->after('OfficersPresent');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'BidSecurityPresent')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->boolean('BidSecurityPresent')->nullable()->after('ReadOutSummary');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'ReceivedOnTime')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->boolean('ReceivedOnTime')->nullable()->after('BidSecurityPresent');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('t_BidSubmissions')) {
            return;
        }
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            foreach ([
                'ReceivedOnTime',
                'BidSecurityPresent',
                'ReadOutSummary',
                'OfficersPresent',
                'CeremonyNotes',
                'CeremonyType',
            ] as $col) {
                if (Schema::hasColumn('t_BidSubmissions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};


