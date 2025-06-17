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
    Schema::table('t_PlanLineItem', function (Blueprint $table) {
        $table->string('SourceType')->nullable()->after('ChangeRemarks');
    });
}

public function down(): void
{
    Schema::table('t_PlanLineItem', function (Blueprint $table) {
        $table->dropColumn('SourceType');
    });
}
};
