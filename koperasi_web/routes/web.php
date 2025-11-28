<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountCodeController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\PostingController;
use App\Http\Controllers\TrialBalanceController;
use App\Http\Controllers\FinanceReportController;

Route::get('/', function () {
    return redirect()->route('account-code-recommender.show');
});
Route::get('/account-code-recommender/{step?}', [AccountCodeController::class, 'index'])
    ->name('account-code-recommender.show');
Route::get('ledger', [LedgerController::class, 'index'])->name('ledger');
Route::get('ledger/export-csv', [LedgerController::class, 'exportCsv'])->name('ledger.export');
Route::get('posting', [PostingController::class, 'index'])->name('posting');
Route::get('posting/export-csv', [PostingController::class, 'exportCsv'])->name('posting.export');
Route::get('trial-balance', [TrialBalanceController::class, 'index'])->name('trial-balance');
Route::get('trial-balance/export-csv', [TrialBalanceController::class, 'exportCsv'])->name('trial-balance.export');
Route::get('finance-report', [FinanceReportController::class, 'index'])->name('finance-report');
Route::get('finance-report/export-csv', [FinanceReportController::class, 'exportCsv'])->name('finance-report.export');

Route::post('/account-code-recommender', [AccountCodeController::class, 'store'])
    ->name('account-code-recommender.store');
Route::post('/process-image', [AccountCodeController::class, 'processImage'])
    ->name('recommender.process');
Route::post('/save-recommendation', [AccountCodeController::class, 'save'])
    ->name('recommender.save');

