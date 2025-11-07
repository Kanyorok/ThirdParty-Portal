<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_CompetitorProducts', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('CompetitorId')->constrained('t_Competitors', 'CompetitorID')->cascadeOnDelete();
            $table->string('Name');
            $table->string('Limit')->nullable();
            $table->string('InterestRate')->nullable();
            $table->string('OtherCharges')->nullable();
            $table->string('RepaymentPeriod')->nullable();
            $table->string('SecurityRequired')->nullable();
            $table->unsignedBigInteger('Clients')->nullable();
            $table->longText('Notes')->nullable();
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
        Schema::dropIfExists('t_CompetitorProducts');
    }
};
