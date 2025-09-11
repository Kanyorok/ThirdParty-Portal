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
        Schema::create('t_CompliancePolicies', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Title', 255);
            $table->foreignId('CategoryID')->nullable()->constrained('t_PolicyCategories','Id');
            $table->foreignId('ComplianceAreaID')->nullable()->constrained('t_ComplianceAreas','Id');
            $table->date('EffectiveDate')->nullable();
            $table->string('Version', 50)->nullable();
            $table->string('FileName', 255)->nullable();
            $table->string('MimeType', 100)->nullable();
            $table->string('FilePath', 500)->nullable();
            $table->boolean('IsActive')->default(1);

            $table->unsignedBigInteger('CreatedBy');
            $table->timestamp('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->timestamp('ModifiedOn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     Schema::dropIfExists('t_CompliancePolicies');
    }
};
