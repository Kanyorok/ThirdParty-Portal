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
        Schema::create('t_BancassuranceCustomersContacts', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('CustomerID');
            $table->date('ContactDate');
            $table->bigInteger('ContactType');
            $table->string('Summary');
            $table->bigInteger('HandledBy')->nullable();
            $table->string('Notes');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_bancas__3214ec07c8c6a7bf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BancassuranceCustomersContacts');
    }
};
