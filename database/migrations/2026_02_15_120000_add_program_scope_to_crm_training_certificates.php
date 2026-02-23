<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('t_CRMTrainingPrograms', 'CertificateScope')) {
            Schema::table('t_CRMTrainingPrograms', function (Blueprint $table) {
                $table->string('CertificateScope', 20)->default('Session');
            });
        }
        if (!Schema::hasColumn('t_CRMTrainingPrograms', 'CertificationCompletionRule')) {
            Schema::table('t_CRMTrainingPrograms', function (Blueprint $table) {
                $table->string('CertificationCompletionRule', 30)->nullable();
            });
        }
        if (!Schema::hasColumn('t_CRMTrainingPrograms', 'CertificationMinimumSessions')) {
            Schema::table('t_CRMTrainingPrograms', function (Blueprint $table) {
                $table->integer('CertificationMinimumSessions')->nullable();
            });
        }

        if (!Schema::hasColumn('t_CRMTrainingCertificates', 'ProgramID')) {
            Schema::table('t_CRMTrainingCertificates', function (Blueprint $table) {
                $table->unsignedBigInteger('ProgramID')->nullable();
            });
        }
        if (!Schema::hasColumn('t_CRMTrainingCertificates', 'CertificateScope')) {
            Schema::table('t_CRMTrainingCertificates', function (Blueprint $table) {
                $table->string('CertificateScope', 20)->default('Session');
            });
        }

        if (!$this->foreignKeyExists('fk_crm_training_certificate_program')) {
            Schema::table('t_CRMTrainingCertificates', function (Blueprint $table) {
                $table->foreign('ProgramID', 'fk_crm_training_certificate_program')
                    ->references('Id')
                    ->on('t_CRMTrainingPrograms');
            });
        }

        DB::statement("
            UPDATE cert
            SET cert.ProgramID = sess.ProgramID
            FROM t_CRMTrainingCertificates cert
            INNER JOIN t_CRMTrainingSessions sess ON sess.Id = cert.SessionID
            WHERE cert.ProgramID IS NULL
        ");

        DB::table('t_CRMTrainingPrograms')
            ->where('HasCertification', 1)
            ->whereNull('CertificationCompletionRule')
            ->update([
                'CertificationCompletionRule' => 'AnySession',
            ]);
    }

    public function down(): void
    {
        if ($this->foreignKeyExists('fk_crm_training_certificate_program')) {
            Schema::table('t_CRMTrainingCertificates', function (Blueprint $table) {
                $table->dropForeign('fk_crm_training_certificate_program');
            });
        }
        if (Schema::hasColumn('t_CRMTrainingCertificates', 'ProgramID')
            || Schema::hasColumn('t_CRMTrainingCertificates', 'CertificateScope')
        ) {
            Schema::table('t_CRMTrainingCertificates', function (Blueprint $table) {
                if (Schema::hasColumn('t_CRMTrainingCertificates', 'ProgramID')) {
                    $table->dropColumn('ProgramID');
                }
                if (Schema::hasColumn('t_CRMTrainingCertificates', 'CertificateScope')) {
                    $table->dropColumn('CertificateScope');
                }
            });
        }

        if (Schema::hasColumn('t_CRMTrainingPrograms', 'CertificateScope')
            || Schema::hasColumn('t_CRMTrainingPrograms', 'CertificationCompletionRule')
            || Schema::hasColumn('t_CRMTrainingPrograms', 'CertificationMinimumSessions')
        ) {
            Schema::table('t_CRMTrainingPrograms', function (Blueprint $table) {
                if (Schema::hasColumn('t_CRMTrainingPrograms', 'CertificateScope')) {
                    $table->dropColumn('CertificateScope');
                }
                if (Schema::hasColumn('t_CRMTrainingPrograms', 'CertificationCompletionRule')) {
                    $table->dropColumn('CertificationCompletionRule');
                }
                if (Schema::hasColumn('t_CRMTrainingPrograms', 'CertificationMinimumSessions')) {
                    $table->dropColumn('CertificationMinimumSessions');
                }
            });
        }
    }

    private function foreignKeyExists(string $constraintName): bool
    {
        return DB::table('sys.foreign_keys')
            ->where('name', $constraintName)
            ->exists();
    }
};
