<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AccountCodeController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\PostingController;
use App\Http\Controllers\TrialBalanceController;
use App\Http\Controllers\SHUReportController;
use App\Http\Controllers\FinancialPositionReportController;
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

    // Jurnal Umum
    Route::get('/ledger', [LedgerController::class, 'index'])->name('ledger');
    Route::get('/ledger/export-csv', [LedgerController::class, 'exportCsv'])->name('ledger.export');

    // Buku Besar Umum
    Route::get('/posting', [PostingController::class, 'index'])->name('posting');
    Route::get('/posting/export-csv', [PostingController::class, 'exportCsv'])->name('posting.export');

    // Neraca 
    Route::get('/trial-balance', [TrialBalanceController::class, 'index'])->name('trial-balance');
    Route::get('/trial-balance/export-csv', [TrialBalanceController::class, 'exportCsv'])->name('trial-balance.export');

    // SHU Report
    Route::get('/shu-report', [SHUReportController::class, 'index'])->name('shu-report');
    Route::get('/shu-report/export-csv', [SHUReportController::class, 'exportCsv'])->name('shu-report.export');

    // Financial Position Report
    Route::get('/financial-position-report', [FinancialPositionReportController::class, 'index'])->name('financial-position-report');
    Route::get('/financial-position-report/export-csv', [FinancialPositionReportController::class, 'exportCsv'])->name('financial-position-report.export');
    
    Route::get('/private-document/{filename}', [AccountCodeController::class, 'showDocument'])
    ->name('document.show');
});