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
        Schema::create('t_RFQEvaluations', function (Blueprint $table) {
            $table->id('Id');
            $table->string('CommitteeMemberName');
            $table->string('UserCode');
            $table->unsignedBigInteger('RFQId'); // foreign to RFQs
            $table->string('RFQComment')->nullable();
            $table->boolean('Confirmation')->default(false);
            $table->foreign('RFQId')->references('Id')->on('t_RFQ')->onDelete('cascade');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RFQEvaluations');
    }
};
