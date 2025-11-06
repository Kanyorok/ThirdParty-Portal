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
        Schema::create('t_DocumentAttributes', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name');
            $table->text('Value');
            $table->char('DataType', 2);
            $table->bigInteger('DocumentId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('VersionId')->nullable();

            $table->primary(['Id'], 'pk__t_docume__3214ec072200c91b');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_DocumentAttributes');
    }
};
