<?php

use Illuminate\Http\Request;




use App\Http\Controllers\Auth\Api\ApiAuthController;
use App\Http\Controllers\Api\PagesApiController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\Chart1Rest;
use App\Http\Controllers\Api\Chart2Rest;
use App\Http\Controllers\Api\Chart3Rest;
use App\Http\Controllers\Api\ClientRest;
use App\Http\Controllers\Api\InvoiceLineRest;
use App\Http\Controllers\Api\InvoiceRest;
use App\Http\Controllers\Api\LeadRest;
use App\Http\Controllers\Api\OfferRest;
use App\Http\Controllers\Api\PaymentRest;
use App\Http\Controllers\Api\ProjectRest;
use App\Http\Controllers\Api\TaskRest;
use App\Http\Controllers\Api\ChartRest;
use App\Http\Controllers\Api\SettingRest;


Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [ApiAuthController::class, 'login']);
    Route::get('refresh', [ApiAuthController::class, 'refresh']);
});


Route::middleware('auth:api')->group(function () {
    
    Route::group(['namespace' => 'App\Api\v1\Controllers'], function () { 
        Route::get('users', ['uses' => 'UserController@index']);
    });

    Route::prefix('auth')->group(function () {
        Route::get('logout', [ApiAuthController::class, 'logout']);
        Route::get('me', [ApiAuthController::class, 'me']);
    });

    Route::prefix('project')->group(function () {
        Route::get('/', [ProjectRest::class, 'index'])->name('api.project.data');
    });

    Route::prefix('task')->group(function () {
        Route::get('/', [TaskRest::class, 'index'])->name('api.task.data');
    });

    Route::prefix('offer')->group(function () {
        Route::get('/', [OfferRest::class, 'getOfferData'])->name('api.offer.data');
    });

    Route::prefix('invoice')->group(function () {
        Route::get('/', [InvoiceRest::class, 'getInvoiceData'])->name('api.invoice.data');
        Route::get('/montantdue', [InvoiceRest::class, 'getMontantDue'])->name('api.invoice.data');
    });

    Route::prefix('invoiceLine')->group(function () {
        Route::get('/', [InvoiceLineRest::class, 'getInvoiceLineData'])->name('api.invoiceLine.data');
    });

    Route::prefix('payment')->group(function () {
        Route::get('/', [PaymentRest::class, 'getPaymentData'])->name('api.payment.data');
        Route::post('/update-amount', [PaymentRest::class, 'updatePaymentAmount'])->name('api.payment.update');
        Route::post('/delete', [PaymentRest::class, 'deletePayment'])->name('api.payment.delete');
    });

    Route::prefix('lead')->group(function () {
        Route::get('/', [LeadRest::class, 'getLeadData'])->name('api.lead.data');
    });

    Route::prefix('chart1')->group(function () {
        Route::get('/', [Chart1Rest::class, 'chart1'])->name('api.chart1.data');
    });
    
    Route::prefix('chart2')->group(function () {
        Route::get('/', [Chart2Rest::class, 'chart2'])->name('api.chart2.data');
    });

    Route::prefix('chart3')->group(function () {
        Route::get('/', [Chart3Rest::class, 'chart3'])->name('api.chart3.data');
    });

    Route::prefix('chart/payment-summary-by-month')->group(function () {
        Route::get('/', [ChartRest::class, 'paymentSummaryByMonth'])->name('api.chart.data');
    });

    Route::get('/chart/task-status-summary', [ChartRest::class, 'taskStatusSummary']);

    Route::get('/chart/invoice-status-summary', [ChartRest::class, 'invoiceStatusSummary']);
    
    Route::get('/dashboard', [PagesApiController::class, 'dashboard']);
    Route::get('/client', [ClientRest::class, 'index']);
    Route::post('/settings/update-discount', [SettingRest::class, 'updateDiscount']);
});