<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\MailLogController;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/domains', [DomainController::class, 'index'])->name('domains');
Route::get('/email-monitor', [MailLogController::class, 'index'])->name('email.monitor');