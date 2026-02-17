<?php

use App\Enums\Core\PermissionEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();
        $actorId = DB::table('t_Modules')->whereNotNull('CreatedBy')->value('CreatedBy')
            ?? DB::table('t_Users')->min('Id')
            ?? 1;
        $modules = [
            ['ModuleID' => 209400, 'Name' => 'Client Trainings', 'Icon' => '<i class="fa fa-user-graduate"></i>', 'Description' => 'Client training and capacity building', 'ParentID' => 200000, 'Route' => null, 'RequiredPermission' => PermissionEnum::ClientRead->value],
            ['ModuleID' => 209410, 'Name' => 'Training Programs', 'Icon' => null, 'Description' => 'Client training programs', 'ParentID' => 209400, 'Route' => 'crm.training.programs.index', 'RequiredPermission' => PermissionEnum::ClientRead->value],
            ['ModuleID' => 209420, 'Name' => 'Training Sessions', 'Icon' => null, 'Description' => 'Scheduled client training sessions', 'ParentID' => 209400, 'Route' => 'crm.training.sessions.index', 'RequiredPermission' => PermissionEnum::ClientRead->value],
            ['ModuleID' => 209430, 'Name' => 'Trainers', 'Icon' => null, 'Description' => 'Client training trainers', 'ParentID' => 209400, 'Route' => 'crm.training.trainers.index', 'RequiredPermission' => PermissionEnum::ClientRead->value],
            ['ModuleID' => 209440, 'Name' => 'Training Categories', 'Icon' => null, 'Description' => 'Client training categories', 'ParentID' => 209400, 'Route' => 'crm.training.categories.index', 'RequiredPermission' => PermissionEnum::ClientRead->value],
            ['ModuleID' => 209450, 'Name' => 'Certificates', 'Icon' => null, 'Description' => 'Client training certificates', 'ParentID' => 209400, 'Route' => 'crm.training.certificates.index', 'RequiredPermission' => PermissionEnum::ClientRead->value],
            ['ModuleID' => 209460, 'Name' => 'Training Reports', 'Icon' => null, 'Description' => 'Client training reports', 'ParentID' => 209400, 'Route' => 'crm.training.reports.index', 'RequiredPermission' => PermissionEnum::ClientRead->value],
        ];

        foreach ($modules as $module) {
            $existing = DB::table('t_Modules')->where('ModuleID', $module['ModuleID'])->first();

            if ($existing) {
                DB::table('t_Modules')->where('ModuleID', $module['ModuleID'])->update([
                    'Name' => $module['Name'],
                    'Icon' => $module['Icon'],
                    'Description' => $module['Description'],
                    'ParentID' => $module['ParentID'],
                    'Route' => $module['Route'],
                    'RequiredPermission' => $module['RequiredPermission'],
                    'ModifiedBy' => $actorId,
                    'ModifiedOn' => $now,
                    'DeletedOn' => null,
                    'DeletedBy' => null,
                ]);
            } else {
                DB::table('t_Modules')->insert([
                    'ModuleID' => $module['ModuleID'],
                    'Name' => $module['Name'],
                    'Icon' => $module['Icon'],
                    'Description' => $module['Description'],
                    'ParentID' => $module['ParentID'],
                    'Route' => $module['Route'],
                    'RequiredPermission' => $module['RequiredPermission'],
                    'CreatedBy' => $actorId,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $actorId,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                    'DeletedOn' => null,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('t_Modules')->whereIn('ModuleID', [209410, 209420, 209430, 209440, 209450, 209460, 209400])->delete();
    }
};
