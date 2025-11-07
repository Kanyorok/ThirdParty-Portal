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
        Schema::create('t_PrequalificationResults', function (Blueprint $table) {
            $table->bigIncrements('ResultID');
            $table->bigInteger('ApplicationID');
            $table->decimal('TotalScore')->nullable();
            $table->string('Decision', 50)->nullable();
            $table->bigInteger('ApprovalBy')->nullable();
            $table->dateTime('ApprovalDate')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['ResultID'], 'pk__t_prequa__9769022862d455de');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PrequalificationResults');
    }
};
