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
        Schema::table('t_BancassuranceCustomersContacts', function (Blueprint $table) {
            $table->foreign(['ContactType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CustomerID'])->references(['Id'])->on('t_BancassuranceCustomers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['HandledBy'])->references(['Id'])->on('t_Employees')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BancassuranceCustomersContacts', function (Blueprint $table) {
            $table->dropForeign('t_bancassurancecustomerscontacts_contacttype_foreign');
            $table->dropForeign('t_bancassurancecustomerscontacts_createdby_foreign');
            $table->dropForeign('t_bancassurancecustomerscontacts_customerid_foreign');
            $table->dropForeign('t_bancassurancecustomerscontacts_deletedby_foreign');
            $table->dropForeign('t_bancassurancecustomerscontacts_handledby_foreign');
            $table->dropForeign('t_bancassurancecustomerscontacts_modifiedby_foreign');
        });
    }
};
