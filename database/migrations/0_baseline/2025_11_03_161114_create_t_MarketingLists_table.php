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
        Schema::create('t_MarketingLists', function (Blueprint $table) {
            $table->bigIncrements('MarketingListID');
            $table->string('slug', 80)->unique();
            $table->string('Label', 200);
            $table->char('Type', 2);
            $table->string('Source', 40);
            $table->text('Extra')->nullable();
            $table->text('Notes')->nullable();
            $table->dateTime('LastContacted')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->char('Visibility', 3)->default('pri');
            $table->text('Processing')->nullable();

            $table->primary(['MarketingListID'], 'pk__t_market__b704b08daa6d0bcc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MarketingLists');
    }
};
