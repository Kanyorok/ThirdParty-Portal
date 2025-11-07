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
        Schema::create('t_Contacts', function (Blueprint $table) {
            $table->bigIncrements('ContactID');
            $table->string('Label', 250);
            $table->string('Email', 200)->nullable();
            $table->string('Phone', 200)->nullable();
            $table->text('Notes')->nullable();
            $table->string('Party');
            $table->string('PartyID', 100);
            $table->text('Extra')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['ContactID'], 'pk__t_contac__5c6625bbda9c9dc8');
            $table->index(['Party', 'PartyID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Contacts');
    }
};
