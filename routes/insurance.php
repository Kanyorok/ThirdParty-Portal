<?php

use App\Http\Controllers\Insurance\BancassuranceReferralController;
use App\Http\Controllers\Insurance\ClaimClosureController;
use App\Http\Controllers\Insurance\ClaimController;
use App\Http\Controllers\Insurance\ClaimPaymentController;
use App\Http\Controllers\Insurance\CommissionEarnedController;
use App\Http\Controllers\Insurance\CommissionPayoutController;
use App\Http\Controllers\Insurance\CommissionRuleController;
use App\Http\Controllers\Insurance\CommissionTierController;
use App\Http\Controllers\Insurance\CustomerBeneficiaryController;
use App\Http\Controllers\Insurance\CustomerCommunicationController;
use App\Http\Controllers\Insurance\CustomerController;
use App\Http\Controllers\Insurance\InsuranceProductController;
use App\Http\Controllers\Insurance\InsuranceProductRiderController;
use App\Http\Controllers\Insurance\InsuranceProviderController;
use App\Http\Controllers\Insurance\InsuranceProviderProductController;
use App\Http\Controllers\Insurance\PolicyController;
use App\Http\Controllers\Insurance\PremiumController;
use App\Http\Controllers\Insurance\PricingRuleController;
use App\Http\Controllers\Insurance\ProductLifecycleController;
use App\Http\Controllers\Insurance\ReportsController;
use App\Http\Controllers\Insurance\SettingsController;
use App\Http\Controllers\Insurance\ClaimClosureController;
use App\Http\Controllers\Insurance\ReportsController;

Route::namespace('Insurance')->prefix('insurance')->group(function () {

    Route::prefix('bancassurance/referrals')->name('bancassurance.referrals.')->group(function () {
        Route::get('create', [BancassuranceReferralController::class, 'create'])->name('create');
        Route::get('edit/{Id}', [BancassuranceReferralController::class, 'edit'])->name('edit');
        Route::get('show/{Id}', [BancassuranceReferralController::class, 'show'])->name('show');
        Route::put('update/{Id}', [BancassuranceReferralController::class, 'update'])->name('update');
        Route::delete('delete/{Id}', [BancassuranceReferralController::class, 'destroy'])->name('destroy');
        Route::post('store', [BancassuranceReferralController::class, 'store'])->name('store');
        Route::get('/', [BancassuranceReferralController::class, 'index'])->name('index');
        Route::get('assign/list', [BancassuranceReferralController::class, 'assignList'])->name('assign.list');
        Route::post('assign/{Id}', [BancassuranceReferralController::class, 'assign'])->name('assign');
        Route::get('performance', [BancassuranceReferralController::class, 'performanceView'])->name('performance');
        Route::get('referrals/products/{insurerId}', [BancassuranceReferralController::class, 'getProductsByInsurer'])->name('referrals.products');
    });

    Route::prefix('bancassurance/customers')->name('bancassurance.customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('check', [CustomerController::class, 'check'])->name('check');
        Route::get('create', [CustomerController::class, 'create'])->name('create');
        Route::post('store', [CustomerController::class, 'store'])->name('store');
        Route::get('show/{Id}', [CustomerController::class, 'show'])->name('show');
        Route::delete('delete/{Id}', [CustomerController::class, 'destroy'])->name('destroy');
        Route::get('edit/{Id}', [CustomerController::class, 'edit'])->name('edit');
        Route::put('update/{Id}', [CustomerController::class, 'update'])->name('update');
        Route::get('{customerId}/portfolio', [CustomerController::class, 'portfolio'])->name('portfolio');
    });
    Route::prefix('bancassurance/customers/beneficiaries')->name('bancassurance.customers.beneficiaries.')->group(function () {
        Route::get('create', [CustomerBeneficiaryController::class, 'create'])->name('create');
        Route::post('store', [CustomerBeneficiaryController::class, 'store'])->name('store');
    });

    Route::prefix('bancassurance/customers/communication')->name('bancassurance.customers.communication.')->group(function () {
        Route::get('/', [CustomerCommunicationController::class, 'index'])->name('index');
        Route::get('create', [CustomerCommunicationController::class, 'create'])->name('create');
        Route::post('store', [CustomerCommunicationController::class, 'store'])->name('store');
        Route::get('show', [CustomerCommunicationController::class, 'show'])->name('show');
        Route::delete('delete/{Id}', [CustomerCommunicationController::class, 'destroy'])->name('destroy');
        Route::get('edit/{Id}', [CustomerCommunicationController::class, 'edit'])->name('edit');
        Route::put('update/{Id}', [CustomerCommunicationController::class, 'update'])->name('update');
    });

    Route::prefix('bancassurance/policies')->name('bancassurance.policies.')->group(function () {
        Route::get('/', [PolicyController::class, 'index'])->name('index');
        Route::get('create', [PolicyController::class, 'create'])->name('create');
        Route::post('store', [PolicyController::class, 'store'])->name('store');
        Route::get('review', [PolicyController::class, 'reviewIndex'])->name('reviewIndex');
        Route::get('products/{insurerId}', [PolicyController::class, 'getProductsByInsurer'])->name('policy.products');
        Route::get('{id}/review', [PolicyController::class, 'review'])->name('review'); // ✅ Add this
        Route::post('{id}/submit', [PolicyController::class, 'submitForUnderwriting'])->name('submitUnderwriting');
        Route::get('{id}/feedback', [PolicyController::class, 'feedbackForm'])->name('feedbackForm');
        Route::get('feedback/list', [PolicyController::class, 'feedbackList'])->name('feedback.list');
        Route::post('{id}/feedback/store', [PolicyController::class, 'storeFeedback'])->name('feedback.store');
        Route::get('{id}/issue', [PolicyController::class, 'issueForm'])->name('issueForm');
        Route::post('{id}/issue/store', [PolicyController::class, 'storeIssuance'])->name('storeIssuance');
        Route::get('issuance/list', [PolicyController::class, 'issuanceList'])->name('issuance.list');
        Route::get('{id}/endorsement', [PolicyController::class, 'endorsementForm'])->name('endorsementForm');
        Route::post('{id}/endorsement/store', [PolicyController::class, 'storeEndorsement'])->name('storeEndorsement');
        Route::get('endorsements/list', [PolicyController::class, 'endorsementList'])->name('endorsements.list');
        Route::get('{id}/endorsement', [PolicyController::class, 'endorsementForm'])->name('endorsementForm');
        Route::get('register', [PolicyController::class, 'register'])->name('register');
        Route::post('{id}/renew/store', [PolicyController::class, 'storeRenewal'])->name('storeRenewal');
        Route::get('{id}/show', [PolicyController::class, 'show'])->name('show');
    });

    Route::prefix('insurance/bancassurance/underwriting')->name('bancassurance.underwriting.')->group(function () {
        Route::get('/', [UnderwritingController::class, 'index'])->name('index');
        Route::get('/review/{id}', [UnderwritingController::class, 'review'])->name('review');
        Route::post('/submit/{id}', [UnderwritingController::class, 'submit'])->name('submit');
    });
    Route::prefix('bancassurance/policies')->name('bancassurance.policies.')->group(function () {
        Route::get('renewals', [PolicyController::class, 'renewalIndex'])->name('renewals.index');
        Route::get('{id}/renew', [PolicyController::class, 'initiateRenewal'])->name('renewalForm');
        // You’ll add store/update later
    });

    Route::prefix('bancassurance/premiums')->name('bancassurance.premiums.')->group(function () {
        Route::get('/', [PremiumController::class, 'index'])->name('index');
        Route::get('create', [PremiumController::class, 'create'])->name('create');
        Route::post('store', [PremiumController::class, 'store'])->name('store');
        Route::get('show/{Id}', [PremiumController::class, 'show'])->name('show');
        Route::delete('delete/{Id}', [PremiumController::class, 'destroy'])->name('destroy');
        Route::get('edit/{Id}', [PremiumController::class, 'edit'])->name('edit');
        Route::get('premiums/{id}/receipt', [PremiumController::class, 'printReceipt'])->name('printReceipt');
        Route::put('update/{Id}', [PremiumController::class, 'update'])->name('update');
    });

    Route::prefix('bancassurance/claims')->name('bancassurance.claims.')->group(function () {
        Route::get('/', [ClaimController::class, 'index'])->name('index');
        Route::get('create', [ClaimController::class, 'create'])->name('create');
        Route::post('store', [ClaimController::class, 'store'])->name('store');
        Route::get('{claimId}/documents', [ClaimController::class, 'documentUploadForm'])->name('documents');
        Route::post('{claimId}/documents/upload', [ClaimController::class, 'uploadDocuments'])->name('documents.upload');
        Route::get('{id}/assess', [ClaimController::class, 'assessForm'])->name('assessForm');
        Route::post('{id}/assess/store', [ClaimController::class, 'storeAssessment'])->name('assess');

        // ✅ Fix these two lines:
        Route::get('{id}/approve', [ClaimController::class, 'approvalForm'])->name('approveForm');
        Route::post('{id}/approve/store', [ClaimController::class, 'storeApproval'])->name('approveStore');
        Route::get('approval/list', [ClaimController::class, 'approvalQueue'])->name('approvalQueue');
        Route::get('{id}/settle', [ClaimController::class, 'paymentForm'])->name('settleForm');
        Route::post('{id}/settle/store', [ClaimController::class, 'storePayment'])->name('settle.store');
        Route::get('payments', [ClaimController::class, 'paymentIndex'])->name('payments.index');
        Route::get('{id}/close', [ClaimClosureController::class, 'closeForm'])->name('closeForm');
        Route::post('{id}/close', [ClaimClosureController::class, 'storeClosure'])->name('storeClosure');
        Route::get('closed', [ClaimClosureController::class, 'closedClaimsIndex'])->name('closed');
        Route::get('initiate-closure', [ClaimClosureController::class, 'initiateClosureForm'])->name('initiateClosureForm');
        Route::post('initiate-closure/store', [ClaimClosureController::class, 'storeClosureFromList'])->name('storeClosureFromList');
    });

    Route::prefix('insurance/bancassurance/claims/payments')->name('bancassurance.claims.payments.')->group(function () {
        Route::get('/', [ClaimPaymentController::class, 'index'])->name('index');  // View all payments
        Route::get('/initiate', [ClaimPaymentController::class, 'create'])->name('initiate'); // List unpaid approved claims
        Route::post('/store', [ClaimPaymentController::class, 'store'])->name('store'); // Save new payment
    });

    Route::prefix('bancassurance/claims')->name('bancassurance.claims.')->group(function () {
        // ...
        Route::get('{id}/close', [ClaimClosureController::class, 'create'])->name('closeForm');
        Route::post('{id}/close/store', [ClaimClosureController::class, 'store'])->name('close');
    });

    Route::prefix('commissions/rules')->name('commissions.rules.')->group(function () {
        Route::get('/', [CommissionRuleController::class, 'index'])->name('index');
        Route::get('create', [CommissionRuleController::class, 'create'])->name('create');
        Route::post('store', [CommissionRuleController::class, 'store'])->name('store');
        Route::get('{Id}/edit', [CommissionRuleController::class, 'edit'])->name('edit');
        Route::put('{Id}/update', [CommissionRuleController::class, 'update'])->name('update');
        Route::delete('{Id}/destroy', [CommissionRuleController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('commissions/tiers')->name('bancassurance.commissions.tiers.')->group(function () {
        Route::get('{ruleId}', [CommissionTierController::class, 'index'])->name('index');
        Route::post('{ruleId}/store', [CommissionTierController::class, 'store'])->name('store');
    });
    Route::prefix('bancassurance/commissions')->name('bancassurance.commissions.')->group(function () {
        // Earned Commissions
        Route::get('earned', [CommissionEarnedController::class, 'index'])->name('earned.index');
        Route::get('earned/{id}/pay', [CommissionEarnedController::class, 'payForm'])->name('earned.payForm');
        Route::post('earned/{id}/pay', [CommissionPayoutController::class, 'storePayout'])->name('earned.storePayout');

        // Payout History
        Route::get('payouts', [CommissionPayoutController::class, 'index'])->name('payouts.index');
        Route::get('payouts/{Id}/pay', [CommissionPayoutController::class, 'pay'])->name('payouts.pay');
        Route::post('payouts/store', [CommissionPayoutController::class, 'store'])->name('payouts.store');
    });

    Route::prefix('bancassurance/insurers')->name('bancassurance.insurers.')->group(function () {
        Route::get('/', [InsuranceProviderController::class, 'index'])->name('index');
        Route::get('create', [InsuranceProviderController::class, 'create'])->name('create');
        Route::post('store', [InsuranceProviderController::class, 'store'])->name('store');
        Route::get('{Id}/edit', [InsuranceProviderController::class, 'edit'])->name('edit');
        Route::put('{Id}/update', [InsuranceProviderController::class, 'update'])->name('update');
        Route::get('{Id}/products', [InsuranceProviderController::class, 'viewProducts'])->name('products');
        Route::delete('delete/{Id}', [InsuranceProviderController::class, 'destroy'])->name('destroy');
        Route::post('{providerId}/products/{productId}/detach', [InsuranceProviderController::class, 'detachProduct'])->name('products.detach');
    });


    Route::prefix('bancassurance/products')->name('bancassurance.products.')->group(function () {

        // Product Setup
        Route::get('/', [InsuranceProductController::class, 'index'])->name('index');
        Route::get('create', [InsuranceProductController::class, 'create'])->name('create');
        Route::post('store', [InsuranceProductController::class, 'store'])->name('store');
        Route::get('{Id}/edit', [InsuranceProductController::class, 'edit'])->name('edit');
        Route::put('{Id}/update', [InsuranceProductController::class, 'update'])->name('update');
        Route::delete('delete/{Id}', [InsuranceProductController::class, 'destroy'])->name('destroy');
        // Route::get('{Id}/map', [InsuranceProductController::class, 'mapForm'])->name('map');
        // Route::post('{Id}/map', [InsuranceProductController::class, 'storeMap'])->name('map.store');


    });

    Route::prefix('bancassurance/products/mapped')->name('bancassurance.products.mapped.')->group(function () {
        Route::get('/', [InsuranceProviderProductController::class, 'index'])->name('index');
        Route::get('create', [InsuranceProviderProductController::class, 'create'])->name('create');
        Route::post('store', [InsuranceProviderProductController::class, 'store'])->name('store');
        Route::delete('mapped/{mappingId}/detach', [InsuranceProviderProductController::class, 'detach'])->name('detach');
    });

    Route::prefix('bancassurance/riders')->name('bancassurance.riders.')->group(function () {
        Route::get('/', [InsuranceProductRiderController::class, 'index'])->name('index');
        Route::get('create', [InsuranceProductRiderController::class, 'create'])->name('create');
        Route::post('store', [InsuranceProductRiderController::class, 'store'])->name('store');
        Route::get('{Id}/edit', [InsuranceProductRiderController::class, 'edit'])->name('edit');
        Route::put('{Id}/update', [InsuranceProductRiderController::class, 'update'])->name('update');
        Route::delete('delete/{Id}', [InsuranceProductRiderController::class, 'destroy'])->name('destroy');
        Route::get('/{ProductId}', [InsuranceProductRiderController::class, 'getProductByProvider'])->name('getProductByProvider');
    });

    Route::prefix('bancassurance/pricing')->name('bancassurance.pricing.')->group(function () {
        Route::get('/', [PricingRuleController::class, 'index'])->name('index');
        Route::get('create', [PricingRuleController::class, 'create'])->name('create');
        Route::post('store', [PricingRuleController::class, 'store'])->name('store');
        Route::get('{Id}/edit', [PricingRuleController::class, 'edit'])->name('edit');
        Route::put('{Id}/update', [PricingRuleController::class, 'update'])->name('update');
        Route::delete('delete/{Id}', [PricingRuleController::class, 'destroy'])->name('destroy');
        Route::get('/{ProductId}', [PricingRuleController::class, 'getProductByProvider'])->name('getProductByProvider');
    });

    Route::prefix('bancassurance/lifecycle')->name('bancassurance.lifecycle.')->group(function () {
        Route::get('/', [ProductLifecycleController::class, 'index'])->name('index');
        Route::post('toggle/{id}', [ProductLifecycleController::class, 'toggleStatus'])->name('toggle');
    });

    Route::prefix('bancassurance/settings')->name('bancassurance.settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/create', [SettingsController::class, 'create'])->name('create'); // ✅ Add this line
        Route::get('/edit/{id}', [SettingsController::class, 'edit'])->name('edit');
        Route::post('/store', [SettingsController::class, 'store'])->name('store');
        Route::put('/update/{id}', [SettingsController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [SettingsController::class, 'destroy'])->name('destroy');
    });

    Route::get('reports/{report}/{format}', [ReportsController::class, 'export'])->name('insurance-reports.export');
    Route::resource('reports', ReportsController::class)->only(['index', 'show'])->names([
        'index' => 'insurance-reports.index',
        'show' => 'insurance-reports.show'
    ]);
});
