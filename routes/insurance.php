<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\bancassurance\BancassuranceReferralController;
use App\Http\Controllers\bancassurance\CustomerController;
use App\Http\Controllers\bancassurance\CustomerBeneficiaryController;
use App\Http\Controllers\bancassurance\CustomerCommunicationController;
use App\Http\Controllers\bancassurance\PolicyController;
use App\Http\Controllers\bancassurance\UnderwritingController;



Route::namespace('Insurance')->prefix('insurance')->group(function () {

    Route::prefix('bancassurance/referrals')->name('bancassurance.referrals.')->group(function () {
    Route::get('create', [BancassuranceReferralController::class, 'create'])->name('create');
    Route::post('store', [BancassuranceReferralController::class, 'store'])->name('store');
    Route::get('/', [BancassuranceReferralController::class, 'index'])->name('index');
    Route::get('assign/list', [BancassuranceReferralController::class, 'assignList'])->name('assign.list');
    Route::post('assign/{id}', [BancassuranceReferralController::class, 'assign'])->name('assign');
    Route::get('performance', [BancassuranceReferralController::class, 'performanceView'])->name('performance');

});

Route::prefix('bancassurance/customers')->name('bancassurance.customers.')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('index');
    Route::get('create', [CustomerController::class, 'create'])->name('create');
    Route::post('store', [CustomerController::class, 'store'])->name('store');
    Route::get('{customerId}/portfolio', [CustomerController::class, 'portfolio'])->name('portfolio');

});
Route::prefix('bancassurance/customers/{customerId}/beneficiaries')->name('bancassurance.customers.beneficiaries.')->group(function () {
    Route::get('create', [CustomerBeneficiaryController::class, 'create'])->name('create');
    Route::post('store', [CustomerBeneficiaryController::class, 'store'])->name('store');
});

Route::prefix('bancassurance/customers/{customerId}/communication')->name('bancassurance.customers.communication.')->group(function () {
    Route::get('/', [CustomerCommunicationController::class, 'index'])->name('index');
    Route::get('create', [CustomerCommunicationController::class, 'create'])->name('create');
    Route::post('store', [CustomerCommunicationController::class, 'store'])->name('store');
});

    Route::prefix('bancassurance/policies')->name('bancassurance.policies.')->group(function () {
    Route::get('/', [PolicyController::class, 'index'])->name('index');
    Route::get('create', [PolicyController::class, 'create'])->name('create');
    Route::post('store', [PolicyController::class, 'store'])->name('store');
});
Route::prefix('insurance/bancassurance/underwriting')->name('bancassurance.underwriting.')->group(function () {
    Route::get('/', [UnderwritingController::class, 'index'])->name('index');
    Route::get('/review/{id}', [UnderwritingController::class, 'review'])->name('review');
    Route::post('/submit/{id}', [UnderwritingController::class, 'submit'])->name('submit');
});

});
