<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('t_ApprovalGroups');
        Schema::dropIfExists('t_ApprovalLimits');
        Schema::dropIfExists('t_PendingWorkflows');

        // Table: t_WorkFlowTypes
        Schema::create('t_WorkFlowTypes', function (Blueprint $table) {
            $table->id('TypeId');
            $table->string('TypeName', 50);
            $table->string('Description')->nullable();
            $table->integer('Count')->default(0);

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->timestamp('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->timestamp('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
        
        // Table: t_WorkFlowStages
        Schema::create('t_WorkFlowStages', function (Blueprint $table) {
            $table->id('Id');
            $table->string('StageCode', 50);
            $table->string('StageName');
            $table->unsignedBigInteger('WorkFlowId');
            $table->unsignedBigInteger('WorkFlowTypeId');
            $table->string('Permission');
            $table->integer('EscalationLimits')->nullable();

            $table->foreign('WorkFlowId')->references('Id')->on('t_WorkFlows')->onDelete('cascade');
            $table->foreign('WorkFlowTypeId')->references('TypeId')->on('t_WorkFlowTypes')->onDelete('cascade');

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->timestamp('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->timestamp('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        // Table: t_WorkFlowHistory
        Schema::create('t_WorkFlowHistory', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('Approver');
            $table->unsignedBigInteger('StatusId');
            $table->string('DocType');
            $table->string('SourceId');

            $table->foreign('Approver')->references('Id')->on('t_Users');
            $table->foreign('StatusId')->references('Id')->on('t_CodeDetails');

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->timestamp('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->timestamp('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        // Table: t_WorkFlowLimits
        Schema::create('t_WorkFlowLimits', function (Blueprint $table) {
            $table->id('Id');
            $table->string('SourceId');
            $table->float('Limit');
            $table->string('Permission');

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->timestamp('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->timestamp('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        // Table: t_WorkFlowPending
        Schema::create('t_WorkFlowPending', function (Blueprint $table) {
            $table->id('Id');
            $table->string('DocType');
            $table->string('SourceId');
            $table->unsignedBigInteger('UserId');
            $table->string('Stage');
            $table->boolean('IsCurrent')->default(true); // track active stage/person
            $table->text('Remarks')->nullable();

            $table->foreign('UserId')->references('Id')->on('t_Users');

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->timestamp('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->timestamp('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->index(['DocType', 'SourceId']);
        });

        // Table: t_WorkFlowDelegation
        Schema::create('t_WorkFlowDelegation', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('UserId');
            $table->string('PreviousRole');
            $table->string('CurrentRole');
            $table->date('StartDate');
            $table->date('EndDate');

            $table->foreign('UserId')->references('Id')->on('t_Users');

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->timestamp('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->timestamp('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_WorkFlowDelegation');
        Schema::dropIfExists('t_WorkFlowPending');
        Schema::dropIfExists('t_WorkFlowLimits');
        Schema::dropIfExists('t_WorkFlowHistory');
        Schema::dropIfExists('t_WorkFlowStages');
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
    }
};
