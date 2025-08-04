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
            $table->id('Id');
            $table->foreignId('CustomerID')->constrained('t_BancassuranceCustomers', 'Id');
            $table->date('ContactDate');
            $table->foreignId('ContactType')->constrained('t_CodeDetails','ID');
            $table->string('Summary');
            $table->foreignId('HandledBy')->nullable()->constrained('t_Employees', 'Id');
            $table->string('Notes');
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
        Schema::dropIfExists('t_BancassuranceCustomersContacts');
    }
};
