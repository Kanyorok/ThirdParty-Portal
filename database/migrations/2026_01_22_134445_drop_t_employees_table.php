<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop all foreign key constraints referencing t_Employees
        // Using actual constraint names from the database
        
        $foreignKeys = [
            ['table' => 't_Users', 'constraint' => 't_users_employeeid_foreign'],
            ['table' => 't_FleetVehicleAssignments', 'constraint' => 't_fleetvehicleassignments_assignedby_foreign'],
            ['table' => 't_FleetContractedDriverAssignments', 'constraint' => 't_fleetcontracteddriverassignments_assignedby_foreign'],
            ['table' => 't_Committee_Employee', 'constraint' => 't_committee_employee_employeeid_foreign'],
            ['table' => 't_FleetVehicleRequests', 'constraint' => 't_fleetvehiclerequests_requestedby_foreign'],
            ['table' => 't_FleetVehicleRequests', 'constraint' => 't_fleetvehiclerequests_approvedby_foreign'],
            ['table' => 't_AssignRequest', 'constraint' => 't_assignrequest_internaltechnician_foreign'],
            ['table' => 't_FleetDriverAssignments', 'constraint' => 't_fleetdriverassignments_assignedby_foreign'],
            ['table' => 't_Departments', 'constraint' => 't_departments_headid_foreign'],
            ['table' => 't_Departments', 'constraint' => 't_departments_deputyheadid_foreign'],
            ['table' => 't_FleetInspectionSchedule', 'constraint' => 't_fleetinspectionschedule_inspector_foreign'],
            ['table' => 't_BancassuranceCustomersContacts', 'constraint' => 't_bancassurancecustomerscontacts_handledby_foreign'],
            ['table' => 't_FleetDrivers', 'constraint' => 't_fleetdrivers_staffnumber_foreign'],
        ];
        
        foreach ($foreignKeys as $fk) {
            if (Schema::hasTable($fk['table'])) {
                try {
                    DB::statement("ALTER TABLE [{$fk['table']}] DROP CONSTRAINT [{$fk['constraint']}]");
                } catch (\Exception $e) {
                    // Constraint doesn't exist or already dropped, continue
                }
            }
        }
        
        // Finally, drop the t_Employees table
        Schema::dropIfExists('t_Employees');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This migration is not reversible as we're dropping the deprecated table
        // The new HR system uses t_HREmployees table instead
        throw new \Exception('This migration cannot be reversed. The t_Employees table has been replaced by t_HREmployees.');
    }
};
