<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_LegalClauses', function (Blueprint $table) {
            $table->id('Id');

            $table->string('Title', 255);
            $table->string('ClauseType', 100);                 // required by your validator
            $table->longText('Content');                       // required by your validator
            $table->string('Version', 50);                     // required by your validator

            // Match your controller's "Yes"/"No"
            $table->string('IsStandard', 3)->default('No');

            $table->string('ClauseDMSDocID', 100)->nullable();

            // Optional governance (safe defaults so controller inserts fine)
            $table->string('Status', 20)->default('ACTIVE');   // ACTIVE|DRAFT|DEPRECATED (optional)
            $table->date('EffectiveFrom')->nullable();
            $table->date('EffectiveTo')->nullable();
            $table->string('Jurisdiction', 120)->nullable();
            $table->json('Tags')->nullable();

            // Audit (matches your model's CREATED_AT/UPDATED_AT names)
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn')->nullable();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            // Indexes
            $table->index(['ClauseType', 'Status'], 'idx_Clauses_Type_Status');
            $table->index('Title', 'idx_Clauses_Title');
            $table->index('Version', 'idx_Clauses_Version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_LegalClauses');
    }
};
