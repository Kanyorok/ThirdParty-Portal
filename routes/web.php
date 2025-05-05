<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Procurement\ItemController;
use App\Http\Controllers\Procurement\ItemCategoryController;

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->namespace('App\Http\Controllers')->group(function () {

    Route::namespace('Settings')->prefix('settings')->group(function () {
        Route::get('lists', 'SettingsController@lists')->name('settings.lists');
        Route::get('users-roles', 'SettingsController@users')->name('settings.users');

        Route::get('integrations', 'SettingsController@integrations')->name('settings.integrations');
        Route::post('integrations', 'IntegrationController');

        Route::post('code-lists-change-order', 'CodeDetailController@order')->name('code-lists.order');
        Route::resource('code-lists', 'CodeDetailController')->parameters(['code-lists' => 'code_detail'])->except(['create', 'show', 'edit']);

        Route::get('locality', 'LocalitySelectController')->name('locality.select2');
        Route::resource('localities', 'LocalityController')->except(['create', 'show', 'edit']);

        Route::post('teams/{team}/notification', 'TeamMessagingController')->name('bulk-notification.team');
        Route::resource('teams/{team}/team-users', 'TeamUserController')->parameters(['team-users' => 'user'])->except(['create', 'edit']);
        Route::resource('teams', 'TeamController')->except(['create', 'edit']);

        Route::resource('meeting-room', 'MeetingRoomController')->except(['edit']);

        Route::resource('branches', 'CrmBranchController')->parameters(['branches' => 'crm_branch'])->except(['edit', 'create', 'show']);

        Route::resource('roles', 'RoleController')->except(['show']);

        Route::namespace('Users')->group(function () {
            Route::prefix('users/{user}')->group(function () {
                Route::resource('user_roles', 'UserRoleController')->only(['index', 'store']);
                Route::get('activities', 'UserActivitiesController')->name('user.activities');
                Route::post('authentication', 'UserActionsController@sendResetLink')->name('users.password.reset');
                Route::post('sync', 'UserActionsController@syncPassword')->name('users.password.sync');
                Route::resource('user-sms', 'UserMessageController')->only(['index', 'store']);
            });

            Route::resource('user-meetings', 'UserMeetingsController')->only('store');
            Route::post('users/notification', 'UserMessagingController')->name('bulk-notification.users');
            Route::get('fetch-users', 'UserSelectController')->name('users.select2');

            Route::resource('users', 'UserController');
        });
    });

    // Route::prefix('procurement')->name('procurement.')->group(function () {
    //     Route::resource('items', ItemController::class);
    //     Route::resource('categories', ItemCategoryController::class);
    // });

    Route::get('help', 'HelpController')->name('help');

    require __DIR__ . '/crm.php';
    require __DIR__ . '/procurement.php';
    require __DIR__ . '/finance.php';
});
