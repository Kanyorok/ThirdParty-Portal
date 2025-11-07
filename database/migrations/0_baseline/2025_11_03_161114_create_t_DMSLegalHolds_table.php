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
        Schema::create('t_DMSLegalHolds', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name');
            $table->string('Ref', 100)->unique();
            $table->text('Description');
            $table->char('Status', 1)->default('A');
            $table->bigInteger('ReleasedBy')->nullable();
            $table->dateTime('ReleasedOn')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_dmsleg__3214ec07923e1df0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_DMSLegalHolds');
    }
};
