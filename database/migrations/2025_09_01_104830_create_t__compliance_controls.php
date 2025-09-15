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
        Schema::create('t_ComplianceControls', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Title', 255);
            $table->text('Description')->nullable();
            $table->foreignId('ComplianceAreaID')->nullable()->constrained('t_ComplianceAreas', 'Id');
            $table->foreignId('ControlTypeID')->nullable()->constrained('t_ControlTypes', 'Id');
            $table->unsignedBigInteger('OwnerID')->nullable(); // from t_Users
            $table->boolean('IsActive')->default(1);

            $table->unsignedBigInteger('CreatedBy');
            $table->timestamp('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->timestamp('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->timestamp('DeletedOn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     Schema::dropIfExists('t_ComplianceControls');
    }
};
