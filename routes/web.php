<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ColocationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MembershipController;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('colocations', ColocationController::class);
});
Route::middleware(['auth'])->group(function () {

    Route::get('/expenses/create', [ExpenseController::class, 'create'])
        ->name('expenses.create');

    Route::post('/expenses', [ExpenseController::class, 'store'])
        ->name('expenses.store');

});
Route::get('/colocations/{colocation}/members', 
    [ColocationController::class, 'members']
)->name('colocations.members');

Route::post('/colocations/{id}/invite', [InvitationController::class, 'store'])
    ->name('invitations.store');

Route::get('/invitations/accept/{token}', [InvitationController::class, 'accept'])
    ->name('invitations.accept');
Route::post('/invitations/refuse/{token}', 
    [InvitationController::class, 'refuse'])
    ->middleware('auth')
    ->name('invitations.refuse'); 
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Membres 
Route::get('/colocations/{colocation}/members', [MembershipController::class, 'index'])->name('colocations.members');   
Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
Route::post('/colocations/{colocation}/invite', 
    [InvitationController::class, 'store']
)->name('invitations.store');
require __DIR__.'/auth.php';
