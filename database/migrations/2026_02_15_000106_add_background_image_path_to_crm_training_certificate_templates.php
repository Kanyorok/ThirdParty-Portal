<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_CRMTrainingCertificateTemplates', function (Blueprint $table) {
            $table->string('BackgroundImagePath', 255)->nullable();
        });

        DB::table('t_CRMTrainingCertificateTemplates')
            ->where('Name', 'Standard Completion Certificate')
            ->update([
                'BackgroundImagePath' => 'assets/certificates/templates/blue-gold-arc.svg',
                'ModifiedOn' => now(),
            ]);

        DB::table('t_CRMTrainingCertificateTemplates')
            ->where('Name', 'Participation Certificate')
            ->update([
                'BackgroundImagePath' => 'assets/certificates/templates/emerald-modern.svg',
                'ModifiedOn' => now(),
            ]);

        $exists = DB::table('t_CRMTrainingCertificateTemplates')
            ->where('Name', 'Executive Completion Certificate')
            ->exists();

        if (!$exists) {
            DB::table('t_CRMTrainingCertificateTemplates')->insert([
                'ProgramID' => null,
                'Name' => 'Executive Completion Certificate',
                'Description' => 'Formal sample style with a ribbon frame.',
                'TemplateBody' => 'This certifies that {{participant_name}} has successfully completed {{program_name}} on {{issue_date}}.',
                'BackgroundImagePath' => 'assets/certificates/templates/slate-ribbon.svg',
                'DefaultIssuingBody' => 'BR_ERP Academy',
                'DefaultValidityMonths' => null,
                'TemplateDocumentId' => null,
                'IsSample' => 1,
                'IsActive' => 1,
                'CreatedBy' => null,
                'CreatedOn' => now(),
                'ModifiedBy' => null,
                'ModifiedOn' => null,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('t_CRMTrainingCertificateTemplates', function (Blueprint $table) {
            $table->dropColumn('BackgroundImagePath');
        });
    }
};
