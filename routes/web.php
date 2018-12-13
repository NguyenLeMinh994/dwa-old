<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::get('auth','HomeController@verifyPortalAPI');
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout')->name('logout');
Route::group(['middleware' => 'localization', 'prefix' => Session::get('locale')], function()
{
    //Route::get('/', 'CustomersController@surveyResults');    
    Route::get('/', 'CustomersController@surveyResults');

    Route::post('lang','LanguageController@postLang');
    Route::post('region','LanguageController@postRegion');
    Route::post('currency','LanguageController@postCurrency');
    
    Route::get('/clear-cache', function() {
        $exitCode = Artisan::call('cache:clear'); echo $exitCode;
        // return what you want
    });
    
    /* ------------------ DASHBOARD ---------------------- */
    Route::get('azure-cost-comparison','AzureCostComparisonController@index')->name('azure-cost-comparison');
    Route::post('azure-cost-comparison/update-csp-discount','AzureCostComparisonController@updateCSPdiscount');
    Route::post('azure-cost-comparison/update-gp-mo-compute','AzureCostComparisonController@updateGPMOCompute');
    Route::post('azure-cost-comparison/update-corrected-compute-ratio','AzureCostComparisonController@updateCorrectedComputeRatio');
    Route::post('azure-cost-comparison/update-weighted-backup-storage','AzureCostComparisonController@updateWeightedBackupStorage');
    Route::post('azure-cost-comparison/update-vm-covered-with-asr-number','AzureCostComparisonController@updateVMCoveredWithASRNumber');
    Route::post('azure-cost-comparison/update-azure-outbound-traffic-cost','AzureCostComparisonController@updateAzureOutboundTrafficCost');
    //Route::post('weighted-backup-storage/update','AzureCostComparisonController@updateBackupStorage');

    Route::get('azure-benefits','AzureBenefitsController@index');
    Route::post('azure-benefits/update-trimming-vms','AzureBenefitsController@updateTrimmingVms');
    Route::post('azure-benefits/update-optimization-benefits','AzureBenefitsController@updateOptimizationBenefits');
    Route::post('azure-benefits/update-allocation-reserved-instance','AzureBenefitsController@updateAllocationReservedInstance');
    Route::post('azure-benefits/update-reversed-instance-adjusted','AzureBenefitsController@updateAdjustedReversedInstance');

    Route::get('business-case','BusinessCaseController@index');
    Route::post('business-case/chart-render','BusinessCaseController@loadChartData');
    Route::post('business-case/update-scenario','BusinessCaseController@updateScenario');
    Route::post('business-case/update-remain-bookingvalues','BusinessCaseController@updateRemainBookingValues');
    Route::post('business-case/update-migration-cost-variables','BusinessCaseController@updateMigrationCostVariables');
    Route::post('business-case/update-microsoft-support-program','BusinessCaseController@updateMicrosoftSupportProgram');

    Route::get('current-cost-structure','CurrentCostStructureController@index');
    Route::get('azure-quality-services','AzureQualityServicesController@index');
    Route::get('current', function(){
        return view('401');
    });
    
    Route::get('customers','CustomersController@index');
    Route::get('questionaires','CustomersController@questionaires');
    Route::get('survey-results','CustomersController@surveyResults')->name('survey-results');
    
    /* ------------------ EXPORT ---------------------- */
    Route::get('export','ExportController@index');
    Route::get('ppt/{ppt_template}','ExportController@exportPowerPoint');
    Route::get('word/{word_template}','ExportController@exportWordDocument');
    Route::get('internal-memo-doc-export','ExportController@exportInternalMemoWordDocument');
    Route::post('update-charts-data','ExportController@updateChartsData');

    //ADMIN ROLE
    //Route::group(['middleware' => 'App\Http\Middleware\UserRole'], function()
    //{
        Route::get('rates','RatecardController@index');
        Route::get('virtual-machine','RatecardController@virtualMachine');

        Route::get('scenario1-calculation','ScenarioCalculationController@viewScenario1');
        Route::get('scenario2-calculation','ScenarioCalculationController@viewScenario2');
        Route::get('scenario3-calculation','ScenarioCalculationController@viewScenario3');

        /* ------------------ COMPARISON ---------------------- */
        Route::resource('variable-stragetic','StrategicVariablesController');
        Route::resource('vm-comparison','VmComparisonController');
        Route::resource('storage-comparison','StorageComparisonController');
        Route::get('cost-comparison','CostComparisonController@index');
        Route::get('variable-comparison','VariableComparisonController@index');
        Route::get('reserved-instances','ReservedInstancesController@index');

        Route::resource('price-categories','PriceCategoriesController');
        Route::resource('storage-categories','StorageCategoriesController');
        Route::resource('asr-categories','ASRCategoriesController');
        Route::resource('valuta','ValutaController');
        Route::resource('currencies','CurrencyController');
        Route::get('currency_suggest', 'ValutaController@autocomplete')->name('currency_suggest');

        // for Vue module
        Route::get('price-input','PriceInputController@priceInput');
        Route::get('suggest-regions','RatecardController@autoSuggestRegions');
        Route::get('suggest-categories','RatecardController@autoSuggestCategories');
        Route::get('suggest-subcategories','RatecardController@autoSuggestSubCategories');
        Route::get('suggest-meters','RatecardController@autoSuggestMeters');
        // ----------------------- //

        Route::get('suggest-vmcategories','RatecardController@autoSuggestVMCategories');
        Route::get('suggest-meter-name','RatecardController@autoSuggestMeterName');
        Route::get('suggest-sub-category','RatecardController@autoSuggestSubCategory');

        //CPU Bench mark
        Route::get('cpu-benchmarks','CPUBenchmarksController@index');
    //});
});

