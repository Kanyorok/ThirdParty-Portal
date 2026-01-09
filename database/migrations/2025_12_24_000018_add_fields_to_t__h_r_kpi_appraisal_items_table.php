<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRKPIAppraisalItems', function (Blueprint $table) {
            $table->unsignedBigInteger('SelfRatingScaleID')->nullable()->after('ActualValue');
            $table->decimal('SelfScore', 8, 2)->nullable()->after('SelfRatingScaleID');
            $table->unsignedBigInteger('SupervisorRatingScaleID')->nullable()->after('SelfScore');
            $table->decimal('FinalScore', 8, 2)->nullable()->after('SupervisorRatingScaleID');
            $table->string('AppraiseeComments', 255)->nullable()->after('FinalScore');
            $table->string('AppraiserComments', 255)->nullable()->after('AppraiseeComments');
        });
    }

    public function down(): void
    {
        Schema::table('t_HRKPIAppraisalItems', function (Blueprint $table) {
            $table->dropColumn([
                'SelfRatingScaleID',
                'SelfScore',
                'SupervisorRatingScaleID',
                'FinalScore',
                'AppraiseeComments',
                'AppraiserComments',
            ]);
        });
    }
};
