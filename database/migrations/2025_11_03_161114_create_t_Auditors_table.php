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
        Schema::create('t_Auditors', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('FirmName');
            $table->string('PhysicalAddress')->nullable();
            $table->string('PostalAddress')->nullable();
            $table->string('Town')->nullable();
            $table->string('Status')->default('Active');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_audito__3214ec07e3303312');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Auditors');
    }
};
