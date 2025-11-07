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
        Schema::create('t_SupplierPreqApplicationDocuments', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('SupplierID');
            $table->bigInteger('RoundID');
            $table->bigInteger('CategoryID');
            $table->bigInteger('SectionID');
            $table->bigInteger('ApplicationID')->nullable()->index();
            $table->string('DocumentId', 50);
            $table->string('FileType')->nullable();
            $table->text('Description')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->index(['SupplierID', 'RoundID', 'CategoryID', 'SectionID'], 'idx_preq_docs_supp_round_cat_sec');
            $table->primary(['Id'], 'pk__t_suppli__3214ec079bc7f9fe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SupplierPreqApplicationDocuments');
    }
};
