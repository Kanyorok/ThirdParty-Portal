<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRExitClearanceDepartments', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRExitClearanceDepartments', 'DepartmentID')) {
                $table->unsignedBigInteger('DepartmentID')->nullable()->after('Name');
                $table->index('DepartmentID', 'IX_HRExitClearanceDepartments_DepartmentID');
                $table->foreign('DepartmentID', 'FK_HRExitClearanceDepartments_Department')
                    ->references('Id')->on('t_Departments');
            }
        });

        $deptRows = DB::table('t_Departments')
            ->whereNull('DeletedOn')
            ->select('Id', 'Name')
            ->get();

        $deptMap = [];
        foreach ($deptRows as $dept) {
            $name = trim((string) $dept->Name);
            if ($name !== '') {
                $deptMap[$name] = $dept->Id;
            }
        }

        $clearanceRows = DB::table('t_HRExitClearanceDepartments')
            ->select('Id', 'Name', 'DepartmentID')
            ->get();

        foreach ($clearanceRows as $row) {
            if (!empty($row->DepartmentID)) {
                continue;
            }
            $name = trim((string) $row->Name);
            if ($name !== '' && isset($deptMap[$name])) {
                DB::table('t_HRExitClearanceDepartments')
                    ->where('Id', $row->Id)
                    ->update(['DepartmentID' => $deptMap[$name]]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('t_HRExitClearanceDepartments', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRExitClearanceDepartments', 'DepartmentID')) {
                $table->dropForeign('FK_HRExitClearanceDepartments_Department');
                $table->dropIndex('IX_HRExitClearanceDepartments_DepartmentID');
                $table->dropColumn('DepartmentID');
            }
        });
    }
};
