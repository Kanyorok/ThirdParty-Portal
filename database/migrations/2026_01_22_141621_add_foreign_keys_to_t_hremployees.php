<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add foreign key constraints to tables that should reference t_HREmployees
     * Using NO ACTION for all constraints to avoid cascade cycles in SQL Server
     */
    public function up(): void
    {
        // Add foreign key constraints to tables referencing t_HREmployees
        // All constraints use NO ACTION (default) to avoid SQL Server cascade path issues
        
        // 1. t_Users.EmployeeId -> t_HREmployees.Id
        if (Schema::hasTable('t_Users') && Schema::hasColumn('t_Users', 'EmployeeId')) {
            // Drop the unique constraint temporarily (it doesn't allow multiple NULLs in SQL Server)
            try {
                DB::statement("DROP INDEX t_users_employeeid_unique ON t_Users");
            } catch (\Exception $e) {
                // Index might not exist
            }
            
            // Clean up orphaned EmployeeId values (set to NULL where employee doesn't exist)
            DB::statement("
                UPDATE t_Users 
                SET EmployeeId = NULL 
                WHERE EmployeeId IS NOT NULL 
                AND NOT EXISTS (
                    SELECT 1 FROM t_HREmployees WHERE t_HREmployees.Id = t_Users.EmployeeId
                )
            ");
            
            // Recreate unique constraint as a filtered index (allows multiple NULLs)
            try {
                DB::statement("
                    CREATE UNIQUE NONCLUSTERED INDEX t_users_employeeid_unique 
                    ON t_Users(EmployeeId) 
                    WHERE EmployeeId IS NOT NULL
                ");
            } catch (\Exception $e) {
                // Index might already exist
            }
            
            // Now add the foreign key
            Schema::table('t_Users', function (Blueprint $table) {
                try {
                    $table->foreign('EmployeeId')
                        ->references('Id')
                        ->on('t_HREmployees');
                } catch (\Exception $e) {
                    // Constraint might already exist
                }
            });
        }
        
        // 2. t_FleetVehicleAssignments.AssignedBy -> t_HREmployees.Id
        if (Schema::hasTable('t_FleetVehicleAssignments') && Schema::hasColumn('t_FleetVehicleAssignments', 'AssignedBy')) {
            DB::statement("
                UPDATE t_FleetVehicleAssignments 
                SET AssignedBy = NULL 
                WHERE AssignedBy IS NOT NULL 
                AND NOT EXISTS (
                    SELECT 1 FROM t_HREmployees WHERE t_HREmployees.Id = t_FleetVehicleAssignments.AssignedBy
                )
            ");
            
            Schema::table('t_FleetVehicleAssignments', function (Blueprint $table) {
                try {
                    $table->foreign('AssignedBy')
                        ->references('Id')
                        ->on('t_HREmployees');
                } catch (\Exception $e) {
                    // Constraint might already exist
                }
            });
        }
        
        // 3. t_FleetContractedDriverAssignments.AssignedBy -> t_HREmployees.Id
        if (Schema::hasTable('t_FleetContractedDriverAssignments') && Schema::hasColumn('t_FleetContractedDriverAssignments', 'AssignedBy')) {
            DB::statement("
                UPDATE t_FleetContractedDriverAssignments 
                SET AssignedBy = NULL 
                WHERE AssignedBy IS NOT NULL 
                AND NOT EXISTS (
                    SELECT 1 FROM t_HREmployees WHERE t_HREmployees.Id = t_FleetContractedDriverAssignments.AssignedBy
                )
            ");
            
            Schema::table('t_FleetContractedDriverAssignments', function (Blueprint $table) {
                try {
                    $table->foreign('AssignedBy')
                        ->references('Id')
                        ->on('t_HREmployees');
                } catch (\Exception $e) {
                    // Constraint might already exist
                }
            });
        }
        
        // 4. t_Committee_Employee.EmployeeId -> t_HREmployees.Id (pivot table)
        if (Schema::hasTable('t_Committee_Employee') && Schema::hasColumn('t_Committee_Employee', 'EmployeeId')) {
            DB::statement("
                DELETE FROM t_Committee_Employee 
                WHERE EmployeeId IS NOT NULL 
                AND NOT EXISTS (
                    SELECT 1 FROM t_HREmployees WHERE t_HREmployees.Id = t_Committee_Employee.EmployeeId
                )
            ");
            
            Schema::table('t_Committee_Employee', function (Blueprint $table) {
                try {
                    $table->foreign('EmployeeId')
                        ->references('Id')
                        ->on('t_HREmployees');
                } catch (\Exception $e) {
                    // Constraint might already exist
                }
            });
        }
        
        // 5. t_FleetVehicleRequests.RequestedBy -> t_HREmployees.Id
        if (Schema::hasTable('t_FleetVehicleRequests') && Schema::hasColumn('t_FleetVehicleRequests', 'RequestedBy')) {
            DB::statement("
                UPDATE t_FleetVehicleRequests 
                SET RequestedBy = NULL 
                WHERE RequestedBy IS NOT NULL 
                AND NOT EXISTS (
                    SELECT 1 FROM t_HREmployees WHERE t_HREmployees.Id = t_FleetVehicleRequests.RequestedBy
                )
            ");
            
            Schema::table('t_FleetVehicleRequests', function (Blueprint $table) {
                try {
                    $table->foreign('RequestedBy')
                        ->references('Id')
                        ->on('t_HREmployees');
                } catch (\Exception $e) {
                    // Constraint might already exist
                }
            });
        }
        
        // 6. t_FleetVehicleRequests.ApprovedBy -> t_HREmployees.Id
        if (Schema::hasTable('t_FleetVehicleRequests') && Schema::hasColumn('t_FleetVehicleRequests', 'ApprovedBy')) {
            DB::statement("
                UPDATE t_FleetVehicleRequests 
                SET ApprovedBy = NULL 
                WHERE ApprovedBy IS NOT NULL 
                AND NOT EXISTS (
                    SELECT 1 FROM t_HREmployees WHERE t_HREmployees.Id = t_FleetVehicleRequests.ApprovedBy
                )
            ");
            
            Schema::table('t_FleetVehicleRequests', function (Blueprint $table) {
                try {
                    $table->foreign('ApprovedBy')
                        ->references('Id')
                        ->on('t_HREmployees');
                } catch (\Exception $e) {
                    // Constraint might already exist
                }
            });
        }
        
        // 7. t_AssignRequest.InternalTechnician -> t_HREmployees.Id
        if (Schema::hasTable('t_AssignRequest') && Schema::hasColumn('t_AssignRequest', 'InternalTechnician')) {
            DB::statement("
                UPDATE t_AssignRequest 
                SET InternalTechnician = NULL 
                WHERE InternalTechnician IS NOT NULL 
                AND NOT EXISTS (
                    SELECT 1 FROM t_HREmployees WHERE t_HREmployees.Id = t_AssignRequest.InternalTechnician
                )
            ");
            
            Schema::table('t_AssignRequest', function (Blueprint $table) {
                try {
                    $table->foreign('InternalTechnician')
                        ->references('Id')
                        ->on('t_HREmployees');
                } catch (\Exception $e) {
                    // Constraint might already exist
                }
            });
        }
        
        // 8. t_FleetDriverAssignments.AssignedBy -> t_HREmployees.Id
        if (Schema::hasTable('t_FleetDriverAssignments') && Schema::hasColumn('t_FleetDriverAssignments', 'AssignedBy')) {
            DB::statement("
                UPDATE t_FleetDriverAssignments 
                SET AssignedBy = NULL 
                WHERE AssignedBy IS NOT NULL 
                AND NOT EXISTS (
                    SELECT 1 FROM t_HREmployees WHERE t_HREmployees.Id = t_FleetDriverAssignments.AssignedBy
                )
            ");
            
            Schema::table('t_FleetDriverAssignments', function (Blueprint $table) {
                try {
                    $table->foreign('AssignedBy')
                        ->references('Id')
                        ->on('t_HREmployees');
                } catch (\Exception $e) {
                    // Constraint might already exist
                }
            });
        }
        
        // 9. t_Departments.HeadId -> t_HREmployees.Id
        if (Schema::hasTable('t_Departments') && Schema::hasColumn('t_Departments', 'HeadId')) {
            DB::statement("
                UPDATE t_Departments 
                SET HeadId = NULL 
                WHERE HeadId IS NOT NULL 
                AND NOT EXISTS (
                    SELECT 1 FROM t_HREmployees WHERE t_HREmployees.Id = t_Departments.HeadId
                )
            ");
            
            Schema::table('t_Departments', function (Blueprint $table) {
                try {
                    $table->foreign('HeadId')
                        ->references('Id')
                        ->on('t_HREmployees');
                } catch (\Exception $e) {
                    // Constraint might already exist
                }
            });
        }
        
        // 10. t_Departments.DeputyHeadId -> t_HREmployees.Id
        if (Schema::hasTable('t_Departments') && Schema::hasColumn('t_Departments', 'DeputyHeadId')) {
            DB::statement("
                UPDATE t_Departments 
                SET DeputyHeadId = NULL 
                WHERE DeputyHeadId IS NOT NULL 
                AND NOT EXISTS (
                    SELECT 1 FROM t_HREmployees WHERE t_HREmployees.Id = t_Departments.DeputyHeadId
                )
            ");
            
            Schema::table('t_Departments', function (Blueprint $table) {
                try {
                    $table->foreign('DeputyHeadId')
                        ->references('Id')
                        ->on('t_HREmployees');
                } catch (\Exception $e) {
                    // Constraint might already exist
                }
            });
        }
        
        // 11. t_FleetInspectionSchedule.Inspector -> t_HREmployees.Id
        if (Schema::hasTable('t_FleetInspectionSchedule') && Schema::hasColumn('t_FleetInspectionSchedule', 'Inspector')) {
            // Inspector column doesn't allow NULLs, so delete orphaned records
            DB::statement("
                DELETE FROM t_FleetInspectionSchedule 
                WHERE Inspector IS NOT NULL 
                AND NOT EXISTS (
                    SELECT 1 FROM t_HREmployees WHERE t_HREmployees.Id = t_FleetInspectionSchedule.Inspector
                )
            ");
            
            Schema::table('t_FleetInspectionSchedule', function (Blueprint $table) {
                try {
                    $table->foreign('Inspector')
                        ->references('Id')
                        ->on('t_HREmployees');
                } catch (\Exception $e) {
                    // Constraint might already exist
                }
            });
        }
        
        // 12. t_BancassuranceCustomersContacts.HandledBy -> t_HREmployees.Id
        if (Schema::hasTable('t_BancassuranceCustomersContacts') && Schema::hasColumn('t_BancassuranceCustomersContacts', 'HandledBy')) {
            DB::statement("
                UPDATE t_BancassuranceCustomersContacts 
                SET HandledBy = NULL 
                WHERE HandledBy IS NOT NULL 
                AND NOT EXISTS (
                    SELECT 1 FROM t_HREmployees WHERE t_HREmployees.Id = t_BancassuranceCustomersContacts.HandledBy
                )
            ");
            
            Schema::table('t_BancassuranceCustomersContacts', function (Blueprint $table) {
                try {
                    $table->foreign('HandledBy')
                        ->references('Id')
                        ->on('t_HREmployees');
                } catch (\Exception $e) {
                    // Constraint might already exist
                }
            });
        }
        
        // 13. t_FleetDrivers.StaffNumber -> t_HREmployees.Id
        if (Schema::hasTable('t_FleetDrivers') && Schema::hasColumn('t_FleetDrivers', 'StaffNumber')) {
            DB::statement("
                UPDATE t_FleetDrivers 
                SET StaffNumber = NULL 
                WHERE StaffNumber IS NOT NULL 
                AND NOT EXISTS (
                    SELECT 1 FROM t_HREmployees WHERE t_HREmployees.Id = t_FleetDrivers.StaffNumber
                )
            ");
            
            Schema::table('t_FleetDrivers', function (Blueprint $table) {
                try {
                    $table->foreign('StaffNumber')
                        ->references('Id')
                        ->on('t_HREmployees');
                } catch (\Exception $e) {
                    // Constraint might already exist
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all foreign key constraints
        $foreignKeys = [
            ['table' => 't_Users', 'column' => 'EmployeeId'],
            ['table' => 't_FleetVehicleAssignments', 'column' => 'AssignedBy'],
            ['table' => 't_FleetContractedDriverAssignments', 'column' => 'AssignedBy'],
            ['table' => 't_Committee_Employee', 'column' => 'EmployeeId'],
            ['table' => 't_FleetVehicleRequests', 'column' => 'RequestedBy'],
            ['table' => 't_FleetVehicleRequests', 'column' => 'ApprovedBy'],
            ['table' => 't_AssignRequest', 'column' => 'InternalTechnician'],
            ['table' => 't_FleetDriverAssignments', 'column' => 'AssignedBy'],
            ['table' => 't_Departments', 'column' => 'HeadId'],
            ['table' => 't_Departments', 'column' => 'DeputyHeadId'],
            ['table' => 't_FleetInspectionSchedule', 'column' => 'Inspector'],
            ['table' => 't_BancassuranceCustomersContacts', 'column' => 'HandledBy'],
            ['table' => 't_FleetDrivers', 'column' => 'StaffNumber'],
        ];
        
        foreach ($foreignKeys as $fk) {
            if (Schema::hasTable($fk['table']) && Schema::hasColumn($fk['table'], $fk['column'])) {
                Schema::table($fk['table'], function (Blueprint $table) use ($fk) {
                    try {
                        $table->dropForeign([$fk['column']]);
                    } catch (\Exception $e) {
                        // Constraint doesn't exist
                    }
                });
            }
        }
    }
};
