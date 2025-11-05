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
        Schema::create('t_SysFilters', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name');
            $table->string('FieldName');
            $table->string('Relation')->nullable();
            $table->string('RelationSource')->nullable();
            $table->char('DataType', 2);
            $table->char('Operator', 2);
            $table->string('Source')->index();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_sysfil__3214ec0751b690d0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SysFilters');
    }
};
