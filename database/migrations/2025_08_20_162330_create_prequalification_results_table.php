<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_PrequalificationResults', function (Blueprint $table) {
            $table->id('ResultID');
            $table->foreignId('ApplicationID')
                ->constrained('t_SupplierPrequalificationApplications', 'ApplicationID')
                ->onDelete('cascade');

            $table->decimal('TotalScore', 8, 2)->nullable();
            $table->string('Decision', 50)->nullable();

            $table->foreignId('ApprovalBy')
                ->nullable()
                ->constrained('t_Users', 'Id')
                ->onDelete('set null');

            $table->timestamp('ApprovalDate')->nullable();

            // Custom timestamps as defined in the model
            $table->timestamp('CreatedOn')->nullable();
            $table->timestamp('ModifiedOn')->nullable();
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_PrequalificationResults');
    }
};
