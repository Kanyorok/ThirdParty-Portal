<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_RFQSection', function (Blueprint $table) {
            // Drop existing foreign key to t_RFQSettingSections if present
            try {
                $table->dropForeign(['SectionID']);
            } catch (\Throwable $e) {
                // ignore if it doesn't exist
            }
        });

        Schema::table('t_RFQSection', function (Blueprint $table) {
            // Recreate FK to t_Sections(Id)
            $table->foreign('SectionID')->references('Id')->on('t_Sections');
        });
    }

    public function down(): void
    {
        Schema::table('t_RFQSection', function (Blueprint $table) {
            try {
                $table->dropForeign(['SectionID']);
            } catch (\Throwable $e) {
            }
        });

        Schema::table('t_RFQSection', function (Blueprint $table) {
            $table->foreign('SectionID')->references('id')->on('t_RFQSettingSections');
        });
    }
};



