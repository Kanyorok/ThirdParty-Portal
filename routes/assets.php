<?php

use App\Http\Controllers\Assets\Accounting\{
    DepreciationRunController,
    ImpairmentController,
    ImprovementController,
    LeaseAssetController,
    RevaluationController
};
use App\Http\Controllers\Assets\Acq\{
    CWIPLineController,
    CWIPProjectController,
    CapitalizationBatchController,
    CapitalizationWizardController,
    DirectCapitalizationController,
    ProcurementLinkController
};
use App\Http\Controllers\Assets\Master\{
    AssetAttachmentController,
    AssetCalibrationController,
    AssetComponentController,
    AssetController,
    AssetHistoryController,
    AssetMeterController,
    AssetMeterReadingController
};
use App\Http\Controllers\Assets\Settings\{
    AssetBookController,
    AssetLocationController,
    AssetNumberingRuleController,
    AssetServiceProviderController,
    DisposalMethodController,
    FixedAssetClassBookController,
    FixedAssetClassController,
    InsuranceTypeController,
    MaintenanceTypeController
};
use Illuminate\Support\Facades\Route;

Route::middleware(['web','auth'])
    ->prefix('assets')
    ->as('assets.')
    ->group(function () {

        Route::prefix('settings')->as('settings.')->group(function () {
            Route::resource('books', AssetBookController::class);
            Route::resource('numbering-rules', AssetNumberingRuleController::class);
            Route::resource('classes', FixedAssetClassController::class);
            Route::resource('class-books', FixedAssetClassBookController::class);
            Route::resource('maintenance-types', MaintenanceTypeController::class);
            Route::resource('disposal-methods', DisposalMethodController::class);
            Route::resource('insurance-types', InsuranceTypeController::class);
            Route::resource('locations', AssetLocationController::class);
            Route::resource('service-providers', AssetServiceProviderController::class);
        });
    });
Route::middleware(['web','auth'])
    ->prefix('assets')
    ->as('assets.')
    ->group(function () {

        /* Existing Settings... */

        /* Master Data */
        Route::prefix('master')->as('master.')->group(function () {

            // Asset Register + Profiles
            Route::resource('register', AssetController::class); // assets.master.register.*

            // Nested resources under a specific asset
            Route::prefix('register/{asset}')->as('register.')->group(function () {
                Route::resource('components', AssetComponentController::class);
                Route::resource('meters', AssetMeterController::class);
                Route::resource('meters.readings', AssetMeterReadingController::class)->shallow();
                Route::resource('calibrations', AssetCalibrationController::class);
                Route::resource('attachments', AssetAttachmentController::class);
                Route::resource('history', AssetHistoryController::class)->only(['index','store','destroy']);
            });
        });
    });



Route::middleware(['web','auth'])
    ->prefix('assets')
    ->as('assets.')
    ->group(function () {

        /* Existing Settings... */

        /* Master Data */
        Route::prefix('master')->as('master.')->group(function () {

            // Asset Register + Profiles
            Route::resource('register', AssetController::class); // assets.master.register.*

            // Nested resources under a specific asset
            Route::prefix('register/{asset}')->as('register.')->group(function () {
                Route::resource('components', AssetComponentController::class);
                Route::resource('meters', AssetMeterController::class);
                Route::resource('meters.readings', AssetMeterReadingController::class)->shallow();
                Route::resource('calibrations', AssetCalibrationController::class);
                Route::resource('attachments', AssetAttachmentController::class);
                Route::resource('history', AssetHistoryController::class)->only(['index','store','destroy']);
            });
        });
    });


Route::middleware(['web','auth'])
    ->prefix('assets')
    ->as('assets.')
    ->group(function () {

        /* --- Acquisition & CWIP --- */
        Route::prefix('acq')->as('acq.')->group(function () {

            // Procurement links (search only)
            Route::get('procurement', [ProcurementLinkController::class, 'index'])->name('procurement.index');

            // CWIP projects + lines
            Route::resource('cwip-projects', CWIPProjectController::class); // assets.acq.cwip-projects.*
            Route::resource('cwip-projects.lines', CWIPLineController::class);// assets.acq.cwip-projects.lines.*

            // Capitalization batches (view)
            Route::resource('cap-batches', CapitalizationBatchController::class)->only(['index','show','destroy']);

            // Capitalization wizard
            Route::get('wizard', [CapitalizationWizardController::class,'index'])->name('wizard.index');
            Route::post('wizard/create-batch', [CapitalizationWizardController::class,'createBatch'])->name('wizard.create-batch');
            Route::post('wizard/post/{batchId}', [CapitalizationWizardController::class,'post'])->name('wizard.post');

            // Direct capitalization (policy-gated)
            Route::get('direct', [DirectCapitalizationController::class,'create'])->name('direct.create');
            Route::post('direct', [DirectCapitalizationController::class,'store'])->name('direct.store');
        });
    });

Route::middleware(['web','auth'])
  ->prefix('assets')
  ->as('assets.')
  ->group(function () {

      /* --- Accounting & Valuation --- */
      Route::prefix('acc')->as('acc.')->group(function () {

          // Depreciation
          Route::get('depruns', [DepreciationRunController::class,'index'])->name('depruns.index');
          Route::get('depruns/create', [DepreciationRunController::class,'create'])->name('depruns.create');
          Route::post('depruns', [DepreciationRunController::class,'store'])->name('depruns.store');
          Route::get('depruns/{id}', [DepreciationRunController::class,'show'])->name('depruns.show');
          Route::post('depruns/{id}/post', [DepreciationRunController::class,'post'])->name('depruns.post');

          // Revaluations
          Route::get('revaluations', [RevaluationController::class,'index'])->name('reval.index');
          Route::get('revaluations/create', [RevaluationController::class,'create'])->name('reval.create');
          Route::post('revaluations', [RevaluationController::class,'store'])->name('reval.store');

          // Impairments
          Route::get('impairments', [ImpairmentController::class,'index'])->name('impair.index');
          Route::get('impairments/create', [ImpairmentController::class,'create'])->name('impair.create');
          Route::post('impairments', [ImpairmentController::class,'store'])->name('impair.store');

          // Improvements
          Route::get('improvements', [ImprovementController::class,'index'])->name('improv.index');
          Route::get('improvements/create', [ImprovementController::class,'create'])->name('improv.create');
          Route::post('improvements', [ImprovementController::class,'store'])->name('improv.store');

          // Leases (optional)
          Route::get('leases', [LeaseAssetController::class,'index'])->name('lease.index');
          Route::get('leases/create', [LeaseAssetController::class,'create'])->name('lease.create');
          Route::post('leases', [LeaseAssetController::class,'store'])->name('lease.store');
      });
  });
