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
        Schema::create('t_BancassuranceCustomers', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->date('DateOfBirth');
            $table->bigInteger('Gender');
            $table->bigInteger('MaritalStatus');
            $table->string('Occupation');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('ReferralID')->nullable();
            $table->bigInteger('ThirdPartyId')->nullable();

            $table->primary(['Id'], 'pk__t_bancas__3214ec07b2926338');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BancassuranceCustomers');
    }
};
