<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColocationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SettlementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Auth routes
require __DIR__.'/auth.php';

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'not.banned'])
    ->name('dashboard');

// Admin routes
Route::prefix('admin')->middleware(['auth', 'not.banned', 'admin'])->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('index');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/mail-test', [AdminController::class, 'mailTest'])->name('mail-test');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/ban', [AdminController::class, 'banUser'])->name('users.ban');
    Route::post('/users/{user}/unban', [AdminController::class, 'unbanUser'])->name('users.unban');
    Route::get('/colocations', [AdminController::class, 'colocations'])->name('colocations');
    Route::get('/expenses', [AdminController::class, 'expenses'])->name('expenses');
    Route::get('/search', [AdminController::class, 'search'])->name('search');
});

// Colocation routes
Route::middleware(['auth', 'not.banned'])->group(function () {
    Route::resource('colocations', ColocationController::class)->only([
        'create', 'store', 'show'
    ]);
    
    Route::get('/colocations/{colocation}/members', [ColocationController::class, 'members'])
        ->name('colocations.members');
    
    Route::post('/colocations/{colocation}/invite', [ColocationController::class, 'invite'])
        ->name('colocations.invite');
    
    Route::post('/colocations/{colocation}/leave', [ColocationController::class, 'leave'])
        ->name('colocations.leave');
    
    Route::post('/colocations/{colocation}/cancel', [ColocationController::class, 'cancel'])
        ->name('colocations.cancel');
    
    Route::delete('/colocations/{colocation}/members/{membership}', [ColocationController::class, 'removeMember'])
        ->name('colocations.remove-member');
});

// Expense routes
Route::middleware(['auth', 'not.banned'])->group(function () {
    Route::get('/colocations/{colocation}/expenses', [ExpenseController::class, 'index'])
        ->name('expenses.index');
    
    Route::get('/colocations/{colocation}/expenses/create', [ExpenseController::class, 'create'])
        ->name('expenses.create');
    
    Route::post('/colocations/{colocation}/expenses', [ExpenseController::class, 'store'])
        ->name('expenses.store');
    
    Route::get('/colocations/{colocation}/expenses/{expense}/edit', [ExpenseController::class, 'edit'])
        ->name('expenses.edit');
    
    Route::put('/colocations/{colocation}/expenses/{expense}', [ExpenseController::class, 'update'])
        ->name('expenses.update');
    
    Route::delete('/colocations/{colocation}/expenses/{expense}', [ExpenseController::class, 'destroy'])
        ->name('expenses.destroy');
});

// Settlement routes
Route::middleware(['auth', 'not.banned'])->group(function () {
    Route::get('/colocations/{colocation}/settlements', [SettlementController::class, 'index'])
        ->name('settlements.index');
    
    Route::post('/colocations/{colocation}/settlements/{settlement}/mark-as-paid', [SettlementController::class, 'markAsPaid'])
        ->name('settlements.mark-as-paid');
    
    Route::post('/colocations/{colocation}/settlements/optimize', [SettlementController::class, 'optimize'])
        ->name('settlements.optimize');
});

// Category routes
Route::middleware(['auth', 'not.banned'])->group(function () {
    Route::get('/colocations/{colocation}/categories', [CategoryController::class, 'index'])
        ->name('categories.index');
    
    Route::post('/colocations/{colocation}/categories', [CategoryController::class, 'store'])
        ->name('categories.store');
    
    Route::get('/colocations/{colocation}/categories/{category}/edit', [CategoryController::class, 'edit'])
        ->name('categories.edit');
    
    Route::put('/colocations/{colocation}/categories/{category}', [CategoryController::class, 'update'])
        ->name('categories.update');
    
    Route::delete('/colocations/{colocation}/categories/{category}', [CategoryController::class, 'destroy'])
        ->name('categories.destroy');
});

// Invitation routes
Route::middleware(['auth', 'not.banned'])->group(function () {
    Route::delete('/colocations/{colocation}/invitations/{invitation}', [ColocationController::class, 'destroyInvitation'])
        ->name('colocations.invitations.destroy');
});

// Public invitation routes (no auth required)
Route::get('/invitations/{token}/accept', [\App\Http\Controllers\InvitationController::class, 'accept'])
    ->name('invitations.accept.public');
Route::post('/invitations/{token}/refuse', [\App\Http\Controllers\InvitationController::class, 'refuse'])
    ->middleware(['auth', 'not.banned'])
    ->name('invitations.refuse');

// Profile routes
Route::middleware(['auth', 'not.banned'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
