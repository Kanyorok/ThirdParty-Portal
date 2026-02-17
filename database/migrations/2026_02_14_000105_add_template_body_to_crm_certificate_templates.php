<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_CRMTrainingCertificateTemplates', function (Blueprint $table) {
            $table->longText('TemplateBody')->nullable();
        });

        DB::table('t_CRMTrainingCertificateTemplates')
            ->where('Name', 'Standard Completion Certificate')
            ->update([
                'TemplateBody' => "This certifies that {{participant_name}} has successfully completed {{program_name}} on {{issue_date}}.",
                'ModifiedOn' => now(),
            ]);

        DB::table('t_CRMTrainingCertificateTemplates')
            ->where('Name', 'Participation Certificate')
            ->update([
                'TemplateBody' => "This certifies that {{participant_name}} participated in {{session_title}} under {{program_name}} on {{issue_date}}.",
                'ModifiedOn' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('t_CRMTrainingCertificateTemplates', function (Blueprint $table) {
            $table->dropColumn('TemplateBody');
        });
    }
};
