<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebauthnController;
use App\Http\Controllers\SecurityActivityController;
use App\Http\Controllers\SecurityDeviceController;

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


    // ============================================================
    // WEBAUTHN LOGIN
    // ============================================================

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

    // ============================================================
    // DASHBOARD
    // ============================================================

    Route::get(
        'dashboard',
        [AuthController::class, 'dashboard']
    )->name('dashboard');


    // ============================================================
    // LOGOUT
    // ============================================================

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


    // ============================================================
    // DEVICE MANAGEMENT
    // ============================================================

    /*
    |--------------------------------------------------------------------------
    | Device List
    |--------------------------------------------------------------------------
    | New SecurityDeviceController handles:
    |
    | - Device search
    | - Device type filter
    | - Operating system filter
    | - Device sorting
    | - Recently used / never used filter
    | - Device security status filter
    */
    Route::get(
        'webauthn/devices',
        [SecurityDeviceController::class, 'index']
    )->name('webauthn.devices.list');


    /*
    |--------------------------------------------------------------------------
    | Device CSV Export
    |--------------------------------------------------------------------------
    |
    | Exports the currently filtered/sorted device list.
    */
    Route::get(
        'webauthn/devices/export',
        [SecurityDeviceController::class, 'export']
    )->name('webauthn.devices.export');


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

    /*
    |--------------------------------------------------------------------------
    | Security Activity Dashboard
    |--------------------------------------------------------------------------
    |
    | New functionality:
    |
    | - Keyword search
    | - Date range filtering
    | - Activity sorting
    | - Existing activity/status/date filters
    */
    Route::get(
        'security/activity',
        [SecurityActivityController::class, 'dashboard']
    )->name('security.activity');


    /*
    |--------------------------------------------------------------------------
    | Security Activity CSV Export
    |--------------------------------------------------------------------------
    |
    | Exports the currently filtered/sorted security activity records.
    */
    Route::get(
        'security/activity/export',
        [SecurityActivityController::class, 'export']
    )->name('security.activity.export');
});