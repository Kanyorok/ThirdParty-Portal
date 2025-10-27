<?php

use App\Http\Controllers\Finance\BankBranchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/auth/heartbeat', function () {
        return response()->noContent();
    })->name('auth.heartbeat');
});

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->namespace('App\Http\Controllers')->group(function () {
    // Customizable dashboard APIs
    Route::prefix('dashboard')->group(function() {
        Route::get('widgets', 'Dashboard\\UserDashboardController@widgets')->name('user-dashboard.widgets');
        Route::post('widgets', 'Dashboard\\UserDashboardController@saveLayout')->name('user-dashboard.widgets.save');
    });
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
