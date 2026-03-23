<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebauthnController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Welcome Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ========== GUEST ROUTES (Not authenticated) ==========
Route::middleware('guest')->group(function () {
    
    // Registration Routes
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.store');
    
    // Login Routes
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.store');
    
    // WebAuthn Login Options
    Route::post('webauthn/login/options', [WebauthnController::class, 'loginOptions'])->name('webauthn.login.options');
    
    // WebAuthn Login
    Route::post('webauthn/login', [WebauthnController::class, 'login'])->name('webauthn.login');
});

// ========== AUTHENTICATED ROUTES ==========
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    
    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    // ===== WebAuthn Routes =====
    
    // Registration Options
    Route::post('webauthn/register/options', [WebauthnController::class, 'registerOptions'])
        ->name('webauthn.register.options');
    
    // Register Device
    Route::post('webauthn/register', [WebauthnController::class, 'register'])
        ->name('webauthn.register');
    
    // List Devices
    Route::get('webauthn/devices', [WebauthnController::class, 'listDevices'])
        ->name('webauthn.devices.list');
    
    // Delete Device
    Route::delete('webauthn/devices/{id}', [WebauthnController::class, 'deleteDevice'])
        ->name('webauthn.devices.delete');
});