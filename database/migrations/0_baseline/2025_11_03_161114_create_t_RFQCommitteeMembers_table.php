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
        Schema::create('t_RFQCommitteeMembers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('CommitteeID');
            $table->bigInteger('UserID');
            $table->bigInteger('RFQID');
            $table->string('Role', 100)->nullable();
            $table->tinyInteger('Response')->default(0);
            $table->boolean('IsActive')->default(true);
            $table->boolean('HasEvaluated')->nullable()->default(false);
            $table->text('reason')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['id'], 'pk__t_rfqcom__3213e83f789a2c78');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RFQCommitteeMembers');
    }
};
