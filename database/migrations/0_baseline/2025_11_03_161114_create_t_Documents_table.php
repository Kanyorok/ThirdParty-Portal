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
        Schema::create('t_Documents', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('DocumentId', 200)->unique();
            $table->string('Name');
            $table->string('MimeType')->nullable();
            $table->bigInteger('CategoryId')->nullable();
            $table->bigInteger('RepositoryId');
            $table->char('Visibility', 3);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_docume__3214ec07616b7da2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Documents');
    }
};
