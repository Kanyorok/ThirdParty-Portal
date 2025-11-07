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
        Schema::create('t_Currencies', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name');
            $table->string('Code')->unique();
            $table->string('Symbol');
            $table->string('SymbolNative');
            $table->integer('DecimalDigits')->default(0);
            $table->decimal('Rounding', 4);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('Demonym')->nullable();
            $table->string('MajorSingle')->nullable();
            $table->string('MajorPlural')->nullable();
            $table->integer('ISOnum')->nullable();
            $table->string('MinorSingle')->nullable();
            $table->string('MinorPlural')->nullable();
            $table->integer('ISOdigits')->nullable();
            $table->integer('Decimals')->nullable();
            $table->integer('NumToBasic')->nullable();

            $table->primary(['Id'], 'pk__t_curren__3214ec075c8884b8');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Currencies');
    }
};
