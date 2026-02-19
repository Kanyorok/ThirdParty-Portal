<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRKPIAppraisalItems', function (Blueprint $table) {
            $table->decimal('SelfRatingValue', 8, 2)->nullable()->after('SelfRatingScaleID');
            $table->decimal('SupervisorRatingValue', 8, 2)->nullable()->after('SupervisorRatingScaleID');
        });
    }

    public function down(): void
    {
        Schema::table('t_HRKPIAppraisalItems', function (Blueprint $table) {
            $table->dropColumn(['SelfRatingValue','SupervisorRatingValue']);
        });
    }
};
