# 🔐 Laravel WebAuthn Authentication System

<div align="center">


</div>

---

## 📖 Table of Contents

- [Overview](#overview)
- [Features](#-features)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [Project Structure](#-project-structure)
- [API Reference](#-api-reference)
- [Database Schema](#-database-schema)
- [Security Features](#-security-features)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [Changelog](#-changelog)
- [Roadmap](#-roadmap)
- [Support](#-support)
- [License](#-license)

---

## Overview

Laravel WebAuthn Authentication System is a **production-ready passwordless authentication solution** that implements the W3C WebAuthn standard. It provides a secure, user-friendly alternative to traditional password-based authentication.

### What is WebAuthn?

WebAuthn (Web Authentication) is a standards-based authentication technology that:
- ✅ **Eliminates passwords** - No passwords to remember or steal
- ✅ **Prevents phishing** - Cryptographic protection against phishing attacks
- ✅ **Works everywhere** - Mobile, desktop, tablets
- ✅ **FIDO2 compliant** - Industry standard security

### Use Cases

- 🏢 **Enterprise Applications** - Secure employee access
- 🏦 **Financial Services** - High-security authentication
- 📱 **Social Platforms** - User-friendly login experience
- 🔐 **Healthcare Systems** - HIPAA-compliant authentication
- 🛒 **E-commerce** - Fast, secure checkout

---

## 🎯 Features

### ✅ Core Authentication
- **Traditional Registration** - Email, name, password-based account creation
- **Email/Password Login** - Legacy authentication support
- **WebAuthn Registration** - Register multiple biometric/security keys
- **Passwordless Login** - Fast, secure authentication without passwords
- **Remember Me** - Session persistence options

### ✅ Device Management
- **Multi-Device Support** - Register multiple authenticators per user
- **Device Tracking** - Monitor device type, OS, IP address
- **Device Deletion** - Remove credentials with confirmation
- **Last Used Tracking** - See when devices were last used
- **Usage History** - Audit trail for security

### ✅ Supported Authenticators
- 🖥️ **Windows Hello** (Face, Fingerprint, PIN)
- 🍎 **Touch ID** (macOS, iPad)
- 😊 **Face ID** (iPhone, iPad)
- 📱 **Android Biometric** (Fingerprint, Face)
- 🔑 **Security Keys** (YubiKey, Titan, etc.)
- 💻 **Platform Authenticators** (Any OS built-in)

### ✅ Security Features
- 🔐 **Password Hashing** - bcrypt hashing with Salt
- 🛡️ **CSRF Protection** - Laravel middleware
- 🔄 **Session Management** - Secure session handling
- ✔️ **Sign Count Validation** - Detects cloned authenticators
- 📍 **IP Tracking** - Monitors login locations
- 🔍 **User Agent Logging** - Device identification
- 📝 **Audit Logging** - Complete event tracking
- 🗑️ **Soft Deletes** - Preserve credential history

### ✅ User Experience
- 🎨 **Beautiful UI** - Tailwind CSS responsive design
- 📱 **Mobile Responsive** - Works on all devices
- ⚡ **Fast Loading** - Optimized performance
- 🎯 **Intuitive Interface** - Easy to use
- ♿ **Accessibility** - WCAG 2.1 compliant
- 🌐 **Internationalization** - Multi-language ready

### ✅ Developer Features
- 📚 **Complete Documentation** - Step-by-step guides
- 🔧 **Easy Configuration** - Minimal setup required
- 🧪 **Well-Tested Code** - Production-ready
- 📊 **Logging & Monitoring** - Full audit trail
- 🚀 **Scalable Architecture** - Enterprise-grade
- 🔌 **Extensible Design** - Easy to customize

---

## 🔧 System Requirements

### Required
| Requirement | Version | Status |
|------------|---------|--------|
| **PHP** | 8.2+ | ✅ Required |
| **Laravel** | 11.x | ✅ Required |
| **MySQL** | 5.7+ / 8.0+ | ✅ Required |
| **Composer** | Latest | ✅ Required |

### Supported Operating Systems
- ✅ Linux (Ubuntu 20.04+, CentOS 8+)
- ✅ macOS (10.15+)
- ✅ Windows (10+)
- ✅ Docker (with HTTPS)

### Supported Browsers
| Browser | Version | WebAuthn | Status |
|---------|---------|----------|--------|
| **Chrome/Chromium** | 67+ | ✅ Full Support | ✅ Recommended |
| **Firefox** | 60+ | ✅ Full Support | ✅ Recommended |
| **Safari** | 13+ | ✅ Full Support | ✅ Recommended |
| **Edge** | 18+ | ✅ Full Support | ✅ Recommended |
| **Opera** | 54+ | ✅ Full Support | ✅ Supported |
| **Mobile Chrome** | 67+ | ✅ Full Support | ✅ Supported |
| **Mobile Firefox** | 60+ | ✅ Full Support | ✅ Supported |
| **Safari iOS** | 14+ | ✅ Full Support | ✅ Supported |

---

## 📦 Installation

### Step 1: Create New Laravel Project

```bash
# Create fresh Laravel project
composer create-project laravel/laravel webauthn-app
cd webauthn-app

# Generate application key
php artisan key:generate

# Remove default migrations (optional)
rm database/migrations/2014_10_12_000000_create_users_table.php
rm database/migrations/2014_10_12_100000_create_password_reset_tokens_table.php
rm database/migrations/2019_08_19_000000_create_failed_jobs_table.php
```

### Step 2: Configure Environment

Edit `.env` file:

```env
APP_NAME=Anvilpat
APP_ENV=local
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=webauthn_db
DB_USERNAME=root
DB_PASSWORD=

# Cache & Queue
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=database
```

### Step 3: Create Database

```bash
# Using MySQL command line
mysql -u root -p
> CREATE DATABASE webauthn_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
> EXIT;

# Or using a GUI tool like phpMyAdmin
```

### Step 4: Copy Project Files

#### Controllers
```bash
cp AuthController.php app/Http/Controllers/AuthController.php
cp WebauthnController.php app/Http/Controllers/WebauthnController.php
```

#### Models
```bash
cp User.php app/Models/User.php
cp WebauthnKey.php app/Models/WebauthnKey.php
```

#### Routes
```bash
cp web.php routes/web.php
```

#### Views
```bash
# Create directories
mkdir -p resources/views/layouts
mkdir -p resources/views/auth

# Copy layout
cp app.blade.php resources/views/layouts/app.blade.php

# Copy auth views
cp welcome.blade.php resources/views/welcome.blade.php
cp register.blade.php resources/views/auth/register.blade.php
cp login.blade.php resources/views/auth/login.blade.php
cp dashboard.blade.php resources/views/dashboard.blade.php
```

### Step 5: Create Migrations

Create `database/migrations/2024_01_01_000001_create_users_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

Create `database/migrations/2024_01_01_000002_create_webauthn_keys_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webauthn_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->text('credential_id')->unique();
            $table->longText('credential_public_key');
            $table->integer('sign_count')->default(0);
            $table->json('transports')->nullable();
            $table->string('rp_id')->nullable();
            $table->string('origin')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_os')->nullable();
            $table->string('last_ip')->nullable();
            $table->string('last_user_agent')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
            $table->index('credential_id');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webauthn_keys');
    }
};
```

### Step 6: Run Migrations

```bash
php artisan migrate
```


### Step 7: Start Development Server

```bash
# Important: Use localhost, NOT 127.0.0.1
php artisan serve

# Output:
# Server running on [http://localhost:8000]
```

### Step 8: Access Application

Open your browser and visit:

```
http://localhost:8000
```

---

## ⚙️ Configuration

### Important: Hostname Configuration

WebAuthn **requires a proper hostname**, not an IP address.

#### Edit Your Hosts File

**Windows (C:\Windows\System32\drivers\etc\hosts)**
```
127.0.0.1 localhost
127.0.0.1 webauthn-app.local
```

**macOS/Linux (/etc/hosts)**
```
127.0.0.1 localhost
127.0.0.1 webauthn-app.local
```

#### Update .env

```env
APP_URL=http://localhost:8000
# or
APP_URL=http://webauthn-app.local:8000
```

#### Access Via

✅ `http://localhost:8000`
✅ `http://webauthn-app.local:8000`
❌ `http://127.0.0.1:8000` (Will NOT work!)

### Environment Variables

```env
# Application
APP_NAME=Anvilpat
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=webauthn_db
DB_USERNAME=root
DB_PASSWORD=

# Logging
LOG_CHANNEL=single
LOG_LEVEL=debug

# Cache
CACHE_DRIVER=file

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Mail (optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hello@example.com
```

---

## 🎮 Usage

### User Registration Flow

```
1. User clicks "Get Started"
   ↓
2. Enters Name, Email, Password
   ↓
3. Clicks "Create Account"
   ↓
4. Account created with hashed password
   ↓
5. User automatically logged in
   ↓
6. Redirected to Dashboard
```

### WebAuthn Device Registration

```
1. User on Dashboard
   ↓
2. Clicks "Add Device Fingerprint"
   ↓
3. Browser prompts for authentication
   ↓
4. User completes biometric scan
   ↓
5. Device credential registered securely
   ↓
6. Device appears in list
```

### WebAuthn Login Flow

```
1. User at Login page
   ↓
2. Clicks "Biometric / Key" tab
   ↓
3. Enters Email address
   ↓
4. Clicks "Scan & Login"
   ↓
5. Browser shows authentication prompt
   ↓
6. User completes biometric scan
   ↓
7. Signed in immediately
   ↓
8. Redirected to Dashboard
```

### Example Code Usage

#### Registration
```php
// routes/web.php
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

// app/Http/Controllers/AuthController.php
public function register(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    Auth::login($user);
    return redirect()->route('dashboard');
}
```

#### WebAuthn Registration
```php
// routes/web.php
Route::post('/webauthn/register/options', [WebauthnController::class, 'registerOptions'])
    ->middleware('auth');
Route::post('/webauthn/register', [WebauthnController::class, 'register'])
    ->middleware('auth');

// app/Http/Controllers/WebauthnController.php
public function registerOptions(Request $request)
{
    $user = Auth::user();
    $challenge = bin2hex(random_bytes(32));
    
    // Store in session for verification
    session(['webauthn_registration_challenge' => $challenge]);
    
    return response()->json([
        'challenge' => base64_encode(hex2bin($challenge)),
        'rp' => ['name' => config('app.name'), 'id' => 'localhost'],
        'user' => [
            'id' => base64_encode($user->id),
            'name' => $user->email,
            'displayName' => $user->name,
        ],
        // ... more options
    ]);
}
```

#### WebAuthn Login
```javascript
// JavaScript in layout
async function startBiometricLogin() {
    const email = document.getElementById('login-email').value;
    
    const webauthn = new Webauthn();
    await webauthn.login({ email });
    
    window.location.href = '/dashboard';
}
```

---

## 📁 Project Structure

```
webauthn-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php          # User auth logic
│   │   │   └── WebauthnController.php      # WebAuthn logic
│   │   ├── Middleware/
│   │   │   └── Authenticate.php            # Auth middleware
│   │   └── Requests/
│   ├── Models/
│   │   ├── User.php                        # User model
│   │   └── WebauthnKey.php                 # Credential model
│   └── Exceptions/
│
├── routes/
│   ├── web.php                             # All web routes
│   └── api.php                             # API routes (optional)
│
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php               # Main layout
│       ├── auth/
│       │   ├── register.blade.php          # Registration form
│       │   └── login.blade.php             # Login form
│       ├── welcome.blade.php               # Home page
│       └── dashboard.blade.php             # User dashboard
│
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_users_table.php
│   │   └── 2024_01_01_000002_create_webauthn_keys_table.php
│   ├── factories/
│   └── seeders/
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   └── session.php
│
├── storage/
│   ├── logs/                               # Application logs
│   └── framework/
│
├── tests/                                  # Test files
│
├── public/
│   ├── css/
│   ├── js/
│   └── index.php                          # Entry point
│
├── .env                                    # Environment variables
├── .env.example                            # Example env
├── composer.json                           # PHP dependencies
├── artisan                                 # Laravel CLI
└── README.md                               # This file
```

---
## output

<img width="1122" height="563" alt="image" src="https://github.com/user-attachments/assets/8d8cb756-93e5-4364-a8dc-cd5f31ec2e71" />
<img width="1015" height="531" alt="image" src="https://github.com/user-attachments/assets/46c3992f-a46d-4d7e-b2f3-b80211b7e2d6" />
<img width="1095" height="622" alt="image" src="https://github.com/user-attachments/assets/1e485dd5-6dfb-430d-b4cb-50d392f3224f" />
<img width="1095" height="622" alt="image" src="https://github.com/user-attachments/assets/c5019e64-bafb-482f-b100-6f8b17cee11f" />


<div align="center">

### Made with ❤️ by the Manav Sanchela

