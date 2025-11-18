<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop legacy tables
        Schema::dropIfExists('t_Workflows');
        Schema::dropIfExists('t_ApprovalGroups');
        Schema::dropIfExists('t_ApprovalLimits');
        Schema::dropIfExists('t_PendingWorkflows');

        Schema::create('t_WorkFlowTypes', static function (Blueprint $table) {
            $table->id('Id');
            $table->char('TypeID', 3)->unique();//AMT, MAJ, ALL, CNT
            $table->string('Name', 100);
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->timestamp('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->timestamp('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });


        Schema::create('t_Workflows', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name', 100)->index();
            $table->string('Source', 100);
            $table->string('FinalStage', 100)->nullable();
            $table->longText('Description')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_WorkFlowLimits', static function (Blueprint $table) {
            $table->id(column: 'Id');
           $table->string('Source')->index();
            $table->decimal('MaxAmount', 20, 4);
            // $table->foreignId('WorkFlowStageId')->constrained('t_WorkFlowStages', 'Id');
            $table->foreignId('PermissionId')->constrained('t_Permissions', 'id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_WorkFlowStages', static function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedSmallInteger('Order');
            $table->string('StageName');
            $table->integer('EscalationLimit')->default(0)->comment('In days');
            $table->foreignId('WorkFlowId')->constrained('t_WorkFlows', 'Id');
            $table->foreignId('WorkFlowTypeId')->constrained('t_WorkFlowTypes', 'Id');
            $table->foreignId('WorkFlowLimitId')->nullable()->constrained('t_WorkFlowLimits', 'Id');
            $table->foreignId('PermissionId')->nullable()->constrained('t_Permissions', 'id');
            $table->integer('Count')->default(0);
            $table->foreignId('StatusId')->nullable()->constrained('t_CodeDetails', 'ID');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->timestamp('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->timestamp('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
         
        Schema::create('t_WorkFlowHistory', static function (Blueprint $table) {
            $table->id('Id');
            $table->string("Source")->comment('PrimaryKey');
            $table->string("SourceID", 100);
            $table->string("Stage", 200);
            $table->decimal('Amount', 20, 4)->nullable();
            $table->longText('Notes')->nullable();
            $table->foreignId('StatusId')->constrained('t_CodeDetails', 'ID');//todo add submitted default for this
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->index(["Source", "SourceID"]);
        });

        Schema::create('t_WorkFlowPending', static function (Blueprint $table) {
            $table->id('Id');
            $table->string("Source");//source with reference if available
            $table->string("SourceID", 100);
            $table->string("Stage", 200);
            $table->foreignId('UserId')->nullable()->comment('assigned')->constrained('t_Users', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->dateTime('EscalatedOn')->nullable();
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->index(["Source", "SourceID"]);
        });

        Schema::create('t_WorkFlowEscalation', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('WorkFlowStageId')->constrained('t_WorkFlowStages', 'Id');
            $table->foreignId('UserId')->constrained('t_Users', 'Id');
            $table->foreignId('SupervisorId')->constrained('t_Users', 'Id');
            $table->text('Notes')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_WorkFlowEscalation');
        Schema::dropIfExists('t_WorkFlowPending');
        Schema::dropIfExists('t_WorkFlowHistory');
        Schema::dropIfExists('t_WorkFlowStages');
        Schema::dropIfExists('t_WorkFlowLimits');
        Schema::dropIfExists('t_Workflows');
        Schema::dropIfExists('t_WorkFlowTypes');

        // Recreate legacy tables
        Schema::create('t_ApprovalGroups', function (Blueprint $table) {
            $table->id('Id');
            $table->string('DocType')->unique();
            $table->string('ApprovalType');
            $table->unsignedBigInteger('Permission');

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->timestamp('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->timestamp('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_ApprovalLimits', function (Blueprint $table) {
            $table->id('Id');
            $table->string('DocType');
            $table->float('MaxAmount');
            $table->string('Permission');

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_PendingWorkflows', static function (Blueprint $table) {
            $table->id('Id');
            $table->string("Source");
            $table->string("SourceID", 100);
            $table->string("Stage", 200);
            $table->foreignId('UserId')->nullable()->comment('assigned')->constrained('t_Users', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->index(["Source", "SourceID"]);
        });

        Schema::create('t_Workflows', static function (Blueprint $table) {
            $table->id('Id');
            $table->string("Source");//source with reference if available
            $table->string("SourceID", 100);
            $table->string("Stage", 200);
            $table->char("Status", 2);
            $table->longText('Notes')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->index(["Source", "SourceID"]);
        });
    }
};
