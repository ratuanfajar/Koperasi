<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountCodeController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\PostingController;
use App\Http\Controllers\TrialBalanceController;
use App\Http\Controllers\FinanceReportController;
use App\Http\Controllers\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/', function () { return redirect()->route('login'); });
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');


Route::middleware('auth')->group(function () {

    // Account Code Recommender
    Route::get('/account-code-recommender/{step?}', [AccountCodeController::class, 'index'])
        ->name('account-code-recommender.show');
    Route::post('/account-code-recommender', [AccountCodeController::class, 'store'])
        ->name('account-code-recommender.store');
    Route::post('/process-image', [AccountCodeController::class, 'processImage'])
        ->name('recommender.process');
    Route::post('/save-recommendation', [AccountCodeController::class, 'save'])
        ->name('recommender.save');

    // Ledger
    Route::get('/dashboard/ledger', [LedgerController::class, 'index'])->name('ledger');
    Route::get('/dashboard/ledger/export-csv', [LedgerController::class, 'exportCsv'])->name('ledger.export');

    // Posting
    Route::get('/dashboard/posting', [PostingController::class, 'index'])->name('posting');
    Route::get('/dashboard/posting/export-csv', [PostingController::class, 'exportCsv'])->name('posting.export');

    // Trial Balance
    Route::get('/dashboard/trial-balance', [TrialBalanceController::class, 'index'])->name('trial-balance');
    Route::get('/dashboard/trial-balance/export-csv', [TrialBalanceController::class, 'exportCsv'])->name('trial-balance.export');

    // Finance Report
    Route::get('/dashboard/finance-report', [FinanceReportController::class, 'index'])->name('finance-report');
    Route::get('/dashboard/finance-report/export-csv', [FinanceReportController::class, 'exportCsv'])->name('finance-report.export');

});