<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AccountCodeController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\PostingController;
use App\Http\Controllers\TrialBalanceController;
use App\Http\Controllers\FinanceReportController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('account-code-recommender.show');
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
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
    Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger');
    Route::get('/ledger/export-csv', [LedgerController::class, 'exportCsv'])->name('ledger.export');

    // Posting
    Route::get('/posting', [PostingController::class, 'index'])->name('posting');
    Route::get('/posting/export-csv', [PostingController::class, 'exportCsv'])->name('posting.export');

    // Trial Balance
    Route::get('/trial-balance', [TrialBalanceController::class, 'index'])->name('trial-balance');
    Route::get('/trial-balance/export-csv', [TrialBalanceController::class, 'exportCsv'])->name('trial-balance.export');

    // Finance Report
    Route::get('/finance-report', [FinanceReportController::class, 'index'])->name('finance-report');
    Route::get('/finance-report/export-csv', [FinanceReportController::class, 'exportCsv'])->name('finance-report.export');

    Route::get('/private-document/{filename}', [AccountCodeController::class, 'showDocument'])
    ->name('document.show');
});