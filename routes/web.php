<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminClientController;
use App\Http\Controllers\AdminAttachmentController;

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('client.dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt')->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Public API Routes for Sage Desktop Sync (protected by token)
Route::get('/api/sage-sync', [ExportController::class, 'apiSageSync'])->name('api.sync');
Route::post('/api/sage-sync/ack', [ExportController::class, 'apiSyncAck'])->name('api.sync.ack');
Route::get('/api/sage-sync/status', [ExportController::class, 'apiSyncStatus'])->name('api.sync.status');

Route::middleware(['auth', 'admin', 'throttle:60,1'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::get('/pending', [ContractController::class, 'pending'])->name('pending');
        Route::get('/create', [ContractController::class, 'create'])->name('create');
        Route::post('/', [ContractController::class, 'store'])->name('store');
        Route::get('/{id}', [ContractController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ContractController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ContractController::class, 'update'])->name('update');
        Route::delete('/{id}', [ContractController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/approve', [ContractController::class, 'approve'])->name('approve');
        Route::post('/{id}/send-email', [ContractController::class, 'sendEmail'])->name('sendEmail');
        Route::get('/{id}/pdf', [InvoiceController::class, 'contractPdf'])->name('pdf');
        Route::get('/{id}/word', [InvoiceController::class, 'contractWord'])->name('word');
        Route::get('/{id}/renew', [ContractController::class, 'renew'])->name('renew');
        Route::post('/{id}/renew', [ContractController::class, 'storeRenewal'])->name('storeRenewal');
        Route::post('/{id}/notify-expiry', [ContractController::class, 'notifyExpiry'])->name('notifyExpiry');
    });

    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [AdminClientController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminClientController::class, 'show'])->name('show');
        Route::put('/{id}', [AdminClientController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminClientController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/attachments/{type}', [AdminAttachmentController::class, 'clientRegistrationAttachment'])
            ->whereIn('type', ['cin', 'company_doc'])
            ->name('attachment');
    });

    Route::get('/contracts/{id}/attachments/{type}', [AdminAttachmentController::class, 'contractAttachment'])
        ->whereIn('type', ['cin', 'certificat'])
        ->name('contracts.attachment');

    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::post('/{id}/status', [InvoiceController::class, 'updateStatus'])->name('status');
        Route::get('/{id}/pdf', [InvoiceController::class, 'downloadPdf'])->name('pdf');
        Route::post('/{id}/send-email', [InvoiceController::class, 'sendEmail'])->name('sendEmail');
    });

    Route::get('/accounting', [AccountingController::class, 'index'])->name('accounting.index');

    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/', [ExportController::class, 'index'])->name('index');
        Route::get('/clients', [ExportController::class, 'exportClients'])->name('clients');
        Route::get('/contracts', [ExportController::class, 'exportContracts'])->name('contracts');
        Route::get('/invoices-txt', [ExportController::class, 'exportInvoicesTxt'])->name('invoices.txt');
        Route::get('/journal-excel', [ExportController::class, 'exportJournalExcel'])->name('journal.excel');
        Route::post('/direct-sync', [ExportController::class, 'directSyncSage'])->name('direct.sync');
    });

    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::post('/data', [ImportController::class, 'importData'])->name('data');
    });

    // Chatbot AI
    Route::post('/chatbot/message', [ChatbotController::class, 'message'])->name('chatbot.message');
});

Route::middleware(['auth', 'client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/demande-contrat', [ClientPortalController::class, 'createContractRequest'])->name('contract.create');
    Route::post('/demande-contrat', [ClientPortalController::class, 'storeContractRequest'])->name('contract.store');
    Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('/contracts/{id}/pdf', [InvoiceController::class, 'contractPdf'])->name('contracts.pdf');
    Route::get('/contracts/{id}/word', [InvoiceController::class, 'contractWord'])->name('contracts.word');
});
