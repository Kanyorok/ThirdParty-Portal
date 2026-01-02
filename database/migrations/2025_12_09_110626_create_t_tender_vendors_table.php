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
        if (!Schema::hasTable('t_TenderVendors')) {
        Schema::create('t_TenderVendors', function (Blueprint $table) {
            // 1. Primary Key matches your Model's $primaryKey
            $table->increments('TenderVendorID'); 

            // 2. Foreign Keys match your Tender Model relations
            $table->unsignedBigInteger('TenderID');
            $table->unsignedBigInteger('SupplierID'); // Matches your Tender model, NOT 'VendorID'

            // 3. Extra columns
            $table->string('InvitationStatus')->nullable();
            
            // 4. Timestamps (Using your custom names if needed, or standard Laravel ones)
            $table->dateTime('CreatedOn')->useCurrent();
            $table->dateTime('ModifiedOn')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            // 5. Foreign Key Constraints
            $table->foreign('TenderID')->references('Id')->on('t_Tenders')->onDelete('cascade');
            $table->foreign('SupplierID')->references('Id')->on('t_Suppliers')->onDelete('cascade');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TenderVendors');
    }
};
