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
        Schema::create('t_TenderStages', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('TenderId');
            $table->string('Stage');
            $table->integer('DurationDays');
            $table->date('StartDate');
            $table->date('EndDate');
            $table->timestamps();
        
            $table->foreign('TenderId')->references('Id')->on('t_Tenders')->onDelete('cascade');
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TenderStages');
    }
};
