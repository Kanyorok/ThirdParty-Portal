<?php

use App\Http\Controllers\Finance\BankBranchController;
use App\Http\Controllers\Settings\WorflowLimitsController;
use App\Http\Controllers\Settings\WorflowLimitController;
use App\Http\Controllers\Settings\WorkFlowController;
use Illuminate\Support\Facades\Route;

// Added for debugging authentication in production
Route::get('/debug/auth', function () {
    return response()->json([
        'auth_check' => auth()->check(),
        'user_id' => auth()->id(),
        'session_id' => session()->getId(),
        'session_driver' => config('session.driver'),
        'session_cookie' => config('session.cookie'),
        'session_domain' => config('session.domain'),
        'secure_cookie' => config('session.secure'),
        'same_site' => config('session.same_site'),
        'app_url' => config('app.url'),
    ]);
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/auth/heartbeat', function () {
        return response()->noContent();
    })->name('auth.heartbeat');
});

require __DIR__ . '/auth.php';

Route::middleware(['web', 'auth'])->namespace('App\Http\Controllers')->group(function () {
    require __DIR__ . '/crm.php';

    // Procurement routes with prefix
    Route::prefix('procurement')->group(function () {
        require __DIR__ . '/procurement.php';
    });

    require __DIR__ . '/inventory.php';
    require __DIR__ . '/property.php';
    require __DIR__ . '/finance.php';
    require __DIR__ . '/fleet.php';
    require __DIR__ . '/insurance.php';
    require __DIR__ . '/dms.php';
    require __DIR__ . '/legal.php';
    require __DIR__ . '/hrms.php';
    require __DIR__ . '/budget.php';
    require __DIR__ . '/thirdparty.php';
    // Prequalification pages temporarily disabled due to stability issues
    require __DIR__ . '/prequalification.php';
    require __DIR__ . '/supplier-cat.php';
    require __DIR__ . '/assets.php';

    Route::namespace('Settings')->prefix('settings')->group(function () {
        Route::get('lists', 'SettingsController@lists')->name('settings.lists');
        Route::get('users-roles', 'SettingsController@users')->name('settings.users');
        Route::get('workflows', [WorkFlowController::class, 'index'])->name('settings.workflows.index');
        Route::post('workflows/create', [WorkFlowController::class, 'store'])->name('settings.workflows.store');
        Route::put('workflows/{id}', [WorkFlowController::class, 'update'])->name('settings.workflows.update');
        // Delete workflow: use proper HTTP verb. Remove old GET delete route.
        Route::delete('workflows/{id}', [WorkFlowController::class, 'destroy'])->name('settings.workflows.destroy');
        // Optional fallback if DELETE is blocked by infra
        Route::post('workflows/delete/{id}', [WorkFlowController::class, 'destroy'])->name('settings.workflows.delete.post');
        Route::get('workflows/{id}', [WorkFlowController::class, 'show'])->name('settings.workflows.show');
        Route::post('workflow-stages', 'WorkflowStagesController@store')->name('settings.workflow_stages.store');
        Route::delete('workflow-stages/{id}', 'WorkflowStagesController@destroy')->name('settings.workflow_stages.destroy');

        Route::get('/workflows/{id}/state', [WorkFlowController::class, 'getState'])->name('settings.workflows.state');



        //         // Add POST alternative for delete to handle form submission
        // Route::post('workflow-stages/{id}', 'WorkflowStagesController@destroy')->name('settings.workflow_stages.destroy.post');
        // Route::delete('workflow-stages/{id}', 'WorkflowStagesController@destroy')->name('settings.workflow_stages.destroy');

        // Workflow Limits Routes
        Route::get('workflow-limits', [WorflowLimitsController::class, 'index'])->name('settings.workflow_limits');
        Route::post('workflow-limits', [WorflowLimitsController::class, 'store'])->name('settings.workflow_limits.store');
        Route::delete('workflow-limits/{id}', [WorflowLimitsController::class, 'destroy'])->name('settings.workflow_limits.destroy');



        Route::get('integrations', 'SettingsController@integrations')->name('settings.integrations');
        Route::post('integrations', 'IntegrationController');
        Route::namespace('Codes')->group(function () {
            Route::resource('currencies', 'CurrencyController')->only('index');

            Route::post('code-lists-change-order', 'CodeDetailController@order')->name('code-lists.order');
            Route::resource('code-lists', 'CodeDetailController')->parameters(['code-lists' => 'code_detail'])->except(['create', 'show', 'edit']);

            Route::get('locality', 'LocalitySelectController')->name('locality.select2');
            Route::resource('localities', 'LocalityController')->except(['create', 'show', 'edit']);
        });

        // User Sessions (single-session admin)
        Route::get('user-sessions', 'UserSessionController@index')->name('settings.user-sessions.index');
        Route::post('user-sessions/revoke/{id}', 'UserSessionController@revoke')->name('settings.user-sessions.revoke');
        Route::post('user-sessions/revoke-others/{userId}', 'UserSessionController@revokeOthers')->name('settings.user-sessions.revoke-others');


        Route::post('teams/{team}/notification', 'TeamMessagingController')->name('bulk-notification.team');
        Route::resource('teams/{team}/team-users', 'TeamUserController')->parameters(['team-users' => 'user'])->except(['create', 'edit']);
        Route::resource('teams', 'TeamController')->except(['create', 'edit']);

        Route::resource('meeting-room', 'MeetingRoomController')->except(['edit']);

        Route::resource('branches', 'CrmBranchController')->parameters(['branches' => 'crm_branch'])->except(['edit', 'create', 'show']);

        Route::resource('roles', 'RoleController'); // remove ->except(['show'])
        Route::post('roles/seed-permissions', 'RoleController@seedPermissions')->name('roles.seedPermissions');
        Route::get('workflow/stage/{stageId}/approvers', [\App\Http\Controllers\Settings\WorkFlowController::class, 'getApprovers'])->name('workflow.stage.approvers');
        Route::get('roles/{id}/ajax', 'RoleController@showAjax')->name('roles.showAjax');


        Route::namespace('Users')->group(function () {
            Route::post('user_roles/branch', 'UserRoleController@storeBranch')->name('user_roles.store_branch');
            // Delete a branch role assignment (under the settings prefix)
            Route::delete('users/branch-role/{modelRole}', 'UserRoleController@destroy')
                ->whereNumber('modelRole')
                ->name('user_roles.delete_branch');
            // Fallback endpoints to operate on ModelRole records identified by composite keys
            // Accept PATCH/DELETE as well so method-overridden forms target these endpoints instead of the {modelRole} parameter route
            Route::post('users/branch-role/delete-by-keys', 'UserRoleController@destroyByKeys')
                ->name('user_roles.delete_branch_by_keys');
            Route::delete('users/branch-role/delete-by-keys', 'UserRoleController@destroyByKeys');

            Route::post('users/branch-role/update-by-keys', 'UserRoleController@updateByKeys')
                ->name('user_roles.update_branch_by_keys');
            Route::patch('users/branch-role/update-by-keys', 'UserRoleController@updateByKeys');

            // Update an existing branch role assignment (route-model binding)
            Route::patch('users/branch-role/{modelRole}', 'UserRoleController@update')
                ->whereNumber('modelRole')
                ->name('user_roles.update_branch');
            Route::prefix('users/{user}')->group(function () {
                Route::resource('user_roles', 'UserRoleController')->only(['index', 'store']);
                Route::get('activities', 'UserActivitiesController')->name('user.activities');
                Route::post('authentication', 'UserActionsController@sendResetLink')->name('users.password.reset');
                Route::post('sync', 'UserActionsController@syncPassword')->name('users.password.sync');
                Route::resource('user-sms', 'UserMessageController')->only(['index', 'store']);
            });

            Route::resource('user-meetings', 'UserMeetingsController')->only(['store', 'destroy']);
            Route::post('users/notification', 'UserMessagingController')->name('bulk-notification.users');
            Route::get('fetch-users', 'UserSelectController')->name('users.select2');

            Route::resource('users', 'UserController');
        });
    });
    Route::get('help', 'HelpController')->name('help');

    // ✅ Global Locality Endpoint
    Route::get('/getCities', [BankBranchController::class, 'getCities'])->name('getCities');
});
// Admin Licensing endpoints (should be accessible post-auth; license check happens after upload)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/license', [LicenseController::class, 'index'])
        ->name('admin.license.index');
    Route::post('/admin/license', [LicenseController::class, 'store'])
        ->name('admin.license.store');
});
