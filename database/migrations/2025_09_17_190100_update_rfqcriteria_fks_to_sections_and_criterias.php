<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_RFQCriteria', function (Blueprint $table) {
            try { $table->dropForeign(['SectionID']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['CriteriaID']); } catch (\Throwable $e) {}
        });

        Schema::table('t_RFQCriteria', function (Blueprint $table) {
            $table->foreign('SectionID')->references('Id')->on('t_Sections');
            $table->foreign('CriteriaID')->references('Id')->on('t_Criterias');
        });
    }

    public function down(): void
    {
        Schema::table('t_RFQCriteria', function (Blueprint $table) {
            try { $table->dropForeign(['SectionID']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['CriteriaID']); } catch (\Throwable $e) {}
        });

        Schema::table('t_RFQCriteria', function (Blueprint $table) {
            $table->foreign('SectionID')->references('id')->on('t_RFQSettingSections');
            $table->foreign('CriteriaID')->references('id')->on('t_RFQSettingCriterias');
        });
    }
};

