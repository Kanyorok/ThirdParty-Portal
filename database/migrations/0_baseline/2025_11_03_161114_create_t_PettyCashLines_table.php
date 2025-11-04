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
        Schema::create('t_PettyCashLines', function (Blueprint $table) {
            $table->bigIncrements('LineID');
            $table->bigInteger('VoucherID')->index();
            $table->bigInteger('GLAccountID')->nullable();
            $table->string('Description', 300)->nullable();
            $table->decimal('Amount', 18)->default(0);
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();

            $table->primary(['LineID'], 'pk__t_pettyc__2eae64c979c5eff7');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PettyCashLines');
    }
};
