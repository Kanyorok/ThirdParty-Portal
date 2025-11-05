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
            $table->bigIncrements('Id');
            $table->string('Title');
            $table->bigInteger('CategoryID')->nullable();
            $table->bigInteger('ComplianceAreaID')->nullable();
            $table->date('EffectiveDate')->nullable();
            $table->string('Version', 50)->nullable();
            $table->string('FileName')->nullable();
            $table->string('MimeType', 100)->nullable();
            $table->string('FilePath', 500)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->primary(['Id'], 'pk__t_compli__3214ec0778977efe');
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
