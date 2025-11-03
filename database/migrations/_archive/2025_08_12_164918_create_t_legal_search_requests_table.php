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
       Schema::create('t_LegalSearchRequests', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('RequestType')->constrained('t_CodeDetails','Value');
            $table->string('EntityName');
            // $table->string('EntityType')->nullable();
            // $table->string('RegistrationNumber');
            // $table->string('Country', 100);
            $table->string('Status')->default('Pending');
            // $table->boolean('IsActive')->default(false);
            $table->text('Remarks');
            $table->longText('Findings')->nullable();
            $table->text('ApprovalReason')->nullable();
            $table->foreignId('RequestedBy')->constrained('t_Users', 'Id');
            $table->dateTime('RequestDate');

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
        Schema::dropIfExists('t_LegalSearchRequests');
    }
};
