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
        // 1. Create role-change history table
        Schema::create('t_CommitteeRoleHistory', function (Blueprint $table) {
            $table->id();
            $table->string('MemberType', 10);          // 'tender' or 'rfq'
            $table->unsignedBigInteger('MemberID');
            $table->unsignedBigInteger('CommitteeID');
            $table->string('PreviousRole', 150)->nullable();
            $table->string('NewRole', 100);
            $table->tinyInteger('Status')->default(0);  // 0=Pending, 1=Accepted, 2=Declined
            $table->unsignedBigInteger('ChangedBy');
            $table->dateTime('ChangedOn');
            $table->dateTime('RespondedOn')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->index(['MemberType', 'MemberID'], 'idx_role_history_member');
            $table->index(['CommitteeID'], 'idx_role_history_committee');
        });

        // 2. Add PendingRole column to tender committee members
        Schema::table('t_TenderCommitteeMembers', function (Blueprint $table) {
            $table->string('PendingRole', 100)->nullable()->after('Role');
        });

        // 3. Add PendingRole column to RFQ committee members
        Schema::table('t_RFQCommitteeMembers', function (Blueprint $table) {
            $table->string('PendingRole', 100)->nullable()->after('Role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_CommitteeRoleHistory');

        Schema::table('t_TenderCommitteeMembers', function (Blueprint $table) {
            $table->dropColumn('PendingRole');
        });

        Schema::table('t_RFQCommitteeMembers', function (Blueprint $table) {
            $table->dropColumn('PendingRole');
        });
    }
};
