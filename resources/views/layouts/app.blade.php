<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Secure Auth')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- WebAuthn Library CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@webauthn/browser@0.3.0/umd/index.js"></script>
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }
        .glass-panel { 
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(10px); 
        }
        .shimmer {
            background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-2 hover:opacity-80 transition">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 text-white rounded-lg flex items-center justify-center font-bold text-lg shadow-lg">A</div>
                <span class="text-xl font-bold text-slate-800 tracking-tight">
                    ANVIL<span class="text-indigo-600">PAT</span>
                </span>
            </a>

            <!-- Navigation Actions -->
            <div class="flex items-center gap-4">
                @auth
                    <span class="text-sm text-slate-600">{{ Auth::user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button class="text-sm font-semibold text-slate-600 hover:text-red-600 transition">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="text-sm font-semibold bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-slate-600">
            <p>🔐 WebAuthn Authentication System | Built with Laravel & Tailwind</p>
        </div>
    </footer>

    <!-- WebAuthn JavaScript Helper -->
    <script>
        class Webauthn {
            /**
             * Register a new WebAuthn credential
             */
            async register() {
                try {
                    // Get registration options from server
                    const optionsResponse = await fetch('/webauthn/register/options', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    });

                    if (!optionsResponse.ok) {
                        throw new Error('Failed to get registration options');
                    }

                    const options = await optionsResponse.json();

                    // Convert base64 to Uint8Array
                    options.challenge = this.bufferDecode(options.challenge);
                    options.user.id = this.bufferDecode(options.user.id);

                    // Call WebAuthn API
                    const attestation = await navigator.credentials.create({
                        publicKey: options
                    });

                    if (!attestation) {
                        throw new Error('Registration was cancelled');
                    }

                    // Send attestation to server
                    const registerResponse = await fetch('/webauthn/register', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: attestation.id,
                            rawId: this.bufferEncode(attestation.rawId),
                            response: {
                                attestationObject: this.bufferEncode(attestation.response.attestationObject),
                                clientDataJSON: this.bufferEncode(attestation.response.clientDataJSON)
                            },
                            type: attestation.type,
                            deviceName: `Device ${new Date().toLocaleDateString()}`
                        })
                    });

                    const result = await registerResponse.json();

                    if (!result.success) {
                        throw new Error(result.message || 'Registration failed');
                    }

                    return result;

                } catch (error) {
                    console.error('WebAuthn Registration Error:', error);
                    throw error;
                }
            }

            /**
             * Login with WebAuthn credential
             */
            async login(options = {}) {
                try {
                    const email = options.email || '';

                    // Get login options from server
                    const optionsResponse = await fetch('/webauthn/login/options', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ email })
                    });

                    if (!optionsResponse.ok) {
                        throw new Error('Failed to get login options');
                    }

                    const loginOptions = await optionsResponse.json();

                    // Convert base64 to Uint8Array
                    loginOptions.challenge = this.bufferDecode(loginOptions.challenge);
                    
                    if (loginOptions.allowCredentials && loginOptions.allowCredentials.length > 0) {
                        loginOptions.allowCredentials = loginOptions.allowCredentials.map(cred => ({
                            ...cred,
                            id: this.bufferDecode(cred.id)
                        }));
                    }

                    // Call WebAuthn API
                    const assertion = await navigator.credentials.get({
                        publicKey: loginOptions,
                        mediation: 'optional'
                    });

                    if (!assertion) {
                        throw new Error('Authentication was cancelled');
                    }

                    // Send assertion to server
                    const loginResponse = await fetch('/webauthn/login', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: assertion.id,
                            rawId: this.bufferEncode(assertion.rawId),
                            response: {
                                authenticatorData: this.bufferEncode(assertion.response.authenticatorData),
                                clientDataJSON: this.bufferEncode(assertion.response.clientDataJSON),
                                signature: this.bufferEncode(assertion.response.signature),
                                userHandle: assertion.response.userHandle ? this.bufferEncode(assertion.response.userHandle) : null,
                                signCount: assertion.response.signCount
                            },
                            type: assertion.type
                        })
                    });

                    const result = await loginResponse.json();

                    if (!result.success) {
                        throw new Error(result.message || 'Authentication failed');
                    }

                    if (result.redirect) {
                        window.location.href = result.redirect;
                    }

                    return result;

                } catch (error) {
                    console.error('WebAuthn Login Error:', error);
                    throw error;
                }
            }

            /**
             * Encode Uint8Array to Base64
             */
            bufferEncode(buffer) {
                return btoa(String.fromCharCode.apply(null, new Uint8Array(buffer)));
            }

            /**
             * Decode Base64 to Uint8Array
             */
            bufferDecode(buffer) {
                return new Uint8Array(atob(buffer).split('').map(c => c.charCodeAt(0)));
            }

            /**
             * Check if WebAuthn is supported
             */
            static isSupported() {
                return window.PublicKeyCredential !== undefined && navigator.credentials !== undefined;
            }
        }

        // Check WebAuthn support on page load
        window.addEventListener('DOMContentLoaded', () => {
            if (!Webauthn.isSupported()) {
                console.warn('WebAuthn is not supported in this browser');
            }
        });
    </script>
</body>
</html>