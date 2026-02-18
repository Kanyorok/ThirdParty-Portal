<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRKPIPerspectiveWeights', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('PerspectiveID');
            $table->unsignedBigInteger('PeriodID');
            $table->unsignedBigInteger('GradeID')->nullable();
            $table->unsignedBigInteger('RoleID')->nullable();
            $table->decimal('Weight', 8, 4)->default(0);
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->unique(['PerspectiveID', 'PeriodID', 'GradeID', 'RoleID'], 'ux_kpi_perspective_weight_scope');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRKPIPerspectiveWeights');
    }
};
