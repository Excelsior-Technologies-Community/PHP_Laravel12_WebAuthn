<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebauthnController;
use App\Http\Controllers\SecurityActivityController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Welcome Page
Route::get('/', function () {
    return view('welcome');
})->name('home');


// ================================================================
// GUEST ROUTES
// ================================================================

Route::middleware('guest')->group(function () {

    // Registration
    Route::get(
        'register',
        [AuthController::class, 'showRegister']
    )->name('register');

    Route::post(
        'register',
        [AuthController::class, 'register']
    )->name('register.store');


    // Login
    Route::get(
        'login',
        [AuthController::class, 'showLogin']
    )->name('login');

    Route::post(
        'login',
        [AuthController::class, 'login']
    )->name('login.store');


    // WebAuthn Login Options
    Route::post(
        'webauthn/login/options',
        [WebauthnController::class, 'loginOptions']
    )->name('webauthn.login.options');


    // WebAuthn Login
    Route::post(
        'webauthn/login',
        [WebauthnController::class, 'login']
    )->name('webauthn.login');
});


// ================================================================
// AUTHENTICATED ROUTES
// ================================================================

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get(
        'dashboard',
        [AuthController::class, 'dashboard']
    )->name('dashboard');


    // Logout
    Route::post(
        'logout',
        [AuthController::class, 'logout']
    )->name('logout');


    // ============================================================
    // WEBAUTHN
    // ============================================================

    // Registration Options
    Route::post(
        'webauthn/register/options',
        [WebauthnController::class, 'registerOptions']
    )->name('webauthn.register.options');


    // Register Device
    Route::post(
        'webauthn/register',
        [WebauthnController::class, 'register']
    )->name('webauthn.register');


    // List Devices
    Route::get(
        'webauthn/devices',
        [WebauthnController::class, 'listDevices']
    )->name('webauthn.devices.list');


    // Rename Device
    Route::put(
        'webauthn/devices/{id}',
        [WebauthnController::class, 'renameDevice']
    )->name('webauthn.devices.rename');


    // Delete Device
    Route::delete(
        'webauthn/devices/{id}',
        [WebauthnController::class, 'deleteDevice']
    )->name('webauthn.devices.delete');


    // ============================================================
    // SECURITY ACTIVITY
    // ============================================================

    Route::get(
        'security/activity',
        [SecurityActivityController::class, 'dashboard']
    )->name('security.activity');
});