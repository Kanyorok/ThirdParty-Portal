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
        Schema::create('t_Approvals', function (Blueprint $table) {
            $table->id('Id');
            $table->string('DocType');
            $table->unsignedBigInteger('DocumentId');
            $table->unsignedBigInteger('UserId');
            $table->string('status', 15)->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->unique(['DocType', 'DocumentId', 'UserId']);

            $table->foreign('UserId')->references('Id')->on('t_Users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Approvals');
    }
};
