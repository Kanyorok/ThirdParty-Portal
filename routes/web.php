<?php

use App\Http\Controllers\Finance\BankBranchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web','auth'])->group(function(){
    Route::get('/auth/heartbeat', function(){
        return response()->noContent();
    })->name('auth.heartbeat');
});

require __DIR__ . '/auth.php';

Route::middleware(['web','auth'])->namespace('App\Http\Controllers')->group(function () {
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
        Route::get('workflows', 'WorkFlowController@index')->name('settings.workflows.index');
        Route::post('workflows/create', 'WorkFlowController@store')->name('settings.workflows.store');
    Route::put('workflows/{id}', 'WorkFlowController@update')->name('settings.workflows.update');
    // Delete workflow: use proper HTTP verb. Remove old GET delete route.
    Route::delete('workflows/{id}', 'WorkFlowController@destroy')->name('settings.workflows.destroy');
    // Optional fallback if DELETE is blocked by infra
    Route::post('workflows/delete/{id}', 'WorkFlowController@destroy')->name('settings.workflows.delete.post');
        Route::get('workflows/{id}', 'WorkFlowController@show')->name('settings.workflows.show');
        Route::post('workflow-stages', 'WorkflowStagesController@store')->name('settings.workflow_stages.store');
        Route::delete('workflow-stages/{id}', 'WorkflowStagesController@destroy')->name('settings.workflow_stages.destroy');
        Route::get('workflow-limits', 'WorflowLimitsController@index')->name('settings.workflow_limits');
        Route::post('workflow-limits', 'WorflowLimitsController@store')->name('settings.approval_workflow_limit.store');

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
        Route::get('roles/{id}/ajax', 'RoleController@showAjax')->name('roles.showAjax');


        Route::namespace('Users')->group(function () {
            Route::post('user_roles/branch', 'UserRoleController@storeBranch')->name('user_roles.store_branch');
            Route::delete('settings/users/branch-role/{modelRole}', 'UserRoleController@destroy')
                ->name('user_roles.delete_branch');
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
