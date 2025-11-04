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
        Schema::create('t_ProductDevelopment', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('ProductID', 100)->unique();
            $table->string('Name');
            $table->string('TargetGroup');
            $table->bigInteger('User_ID')->nullable();
            $table->decimal('Income', 25)->nullable();
            $table->decimal('Revenue', 25)->nullable();
            $table->text('Regulatory')->nullable();
            $table->text('Notes')->nullable();
            $table->text('Justification')->nullable();
            $table->text('Risks')->nullable();
            $table->text('RiskStrategies')->nullable();
            $table->text('Summary')->nullable();
            $table->bigInteger('StageId');
            $table->bigInteger('ArchivedBy')->nullable();
            $table->dateTime('ArchivedOn')->nullable();
            $table->dateTime('CommentStart')->nullable();
            $table->dateTime('CommentEnd')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_produc__3214ec07ad35fa56');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ProductDevelopment');
    }
};
