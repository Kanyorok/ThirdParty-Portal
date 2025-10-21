<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_SupplierPreqApplicationDocuments', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('SupplierID');
            $table->unsignedBigInteger('RoundID');
            $table->unsignedBigInteger('CategoryID');
            $table->unsignedBigInteger('SectionID');
            $table->unsignedBigInteger('ApplicationID')->nullable();
            $table->unsignedBigInteger('DocumentId'); // DMS Document primary key
            $table->string('FileType')->nullable();
            $table->text('Description')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->timestamp('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->timestamp('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->softDeletes('DeletedOn');

            $table->index(['SupplierID', 'RoundID', 'CategoryID', 'SectionID'], 'idx_preq_docs_supp_round_cat_sec');
            $table->index(['ApplicationID']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_SupplierPreqApplicationDocuments');
    }
};
