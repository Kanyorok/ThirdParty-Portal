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
        Schema::create('t_Tenders', function (Blueprint $table) {
            $table->id('Id');
            $table->string('TenderNumber')->unique()->comment('Unique identifier for the tender');
            $table->string('Title')->comment('Title of the tender');
            $table->text('Description')->comment('Detailed description of the tender');
            $table->foreignId('ProcurementModeId')->constrained('t_ProcurementModes')->onDelete('cascade')->comment('Foreign key to procurement modes');
            $table->date('StartDate')->comment('Start date of the tender');
            $table->enum('Status', ['open', 'closed', 'awarded', 'cancelled'])->default('open')->comment('Current status of the tender');
            $table->decimal('EstimatedValue', 15, 2)->comment('Estimated value of the tender');
            $table->string('Currency', 10)->comment('Currency of the estimated value');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id')->comment('User who created the tender');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id')->comment('User who last modified the tender');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t__Tenders');
    }
};
