<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'Secure Auth')
    </title>


    <!-- ============================================================
         TAILWIND CSS
    ============================================================ -->

    <script src="https://cdn.tailwindcss.com"></script>


    <!-- ============================================================
         GOOGLE FONT
    ============================================================ -->

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- ============================================================
         WEBAUTHN BROWSER LIBRARY
    ============================================================ -->

    <script
        src="https://cdn.jsdelivr.net/npm/@webauthn/browser@0.3.0/umd/index.js"
    ></script>


    <!-- ============================================================
         CUSTOM STYLES
    ============================================================ -->

    <style>

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .shimmer {
            background:
                linear-gradient(
                    90deg,
                    #f3f4f6 25%,
                    #e5e7eb 50%,
                    #f3f4f6 75%
                );

            background-size: 200% 100%;

            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {

            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }

        }

        .nav-link {
            transition:
                color 0.2s ease,
                background-color 0.2s ease;
        }

        .nav-link:hover {
            color: #4f46e5;
        }

        .nav-active {
            color: #4f46e5 !important;
        }

    </style>

</head>


<body class="bg-slate-50 min-h-screen flex flex-col">


    <!-- ============================================================
         NAVIGATION
    ============================================================ -->

    <nav
        class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm"
    >

        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between"
        >


            <!-- ====================================================
                 LOGO
            ==================================================== -->

            <a
                href="{{ url('/') }}"
                class="flex items-center gap-2 hover:opacity-80 transition"
            >

                <div
                    class="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 text-white rounded-lg flex items-center justify-center font-bold text-lg shadow-lg"
                >
                    A
                </div>

                <span
                    class="text-xl font-bold text-slate-800 tracking-tight"
                >
                    ANVIL<span class="text-indigo-600">PAT</span>
                </span>

            </a>


            <!-- ====================================================
                 NAVIGATION ACTIONS
            ==================================================== -->

            <div class="flex items-center gap-4">

                @auth

                    <!-- Dashboard -->

                    <a
                        href="{{ route('dashboard') }}"
                        class="nav-link text-sm font-semibold text-slate-600"
                    >
                        Dashboard
                    </a>


                    <!-- Security Activity -->

                    <a
                        href="{{ route('security.activity') }}"
                        class="
                            nav-link
                            text-sm
                            font-semibold
                            text-slate-600
                        "
                    >
                        Security
                    </a>


                    <!-- WebAuthn Devices -->

                    <a
                        href="{{ route('webauthn.devices.list') }}"
                        class="
                            nav-link
                            text-sm
                            font-semibold
                            text-slate-600
                        "
                    >
                        Devices
                    </a>


                    <!-- User Name -->

                    <span
                        class="text-sm text-slate-600 hidden sm:inline"
                    >
                        {{ Auth::user()->name }}
                    </span>


                    <!-- Logout -->

                    <form
                        action="{{ route('logout') }}"
                        method="POST"
                        class="inline"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="
                                text-sm
                                font-semibold
                                text-slate-600
                                hover:text-red-600
                                transition
                            "
                        >
                            Logout
                        </button>

                    </form>

                @else

                    <!-- Login -->

                    <a
                        href="{{ route('login') }}"
                        class="
                            nav-link
                            text-sm
                            font-semibold
                            text-slate-600
                        "
                    >
                        Login
                    </a>


                    <!-- Register -->

                    <a
                        href="{{ route('register') }}"
                        class="
                            text-sm
                            font-semibold
                            bg-indigo-600
                            text-white
                            px-4
                            py-2
                            rounded-lg
                            hover:bg-indigo-700
                            transition
                            shadow-lg
                            shadow-indigo-200
                        "
                    >
                        Register
                    </a>

                @endauth

            </div>

        </div>

    </nav>


    <!-- ============================================================
         FLASH MESSAGES
    ============================================================ -->

    @if(session('success'))

        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-5">

            <div
                class="
                    bg-green-50
                    border
                    border-green-200
                    text-green-800
                    px-4
                    py-3
                    rounded-xl
                    shadow-sm
                "
            >

                <div class="flex items-center gap-2">

                    <span class="text-lg">
                        ✓
                    </span>

                    <span class="text-sm font-medium">
                        {{ session('success') }}
                    </span>

                </div>

            </div>

        </div>

    @endif


    @if(session('error'))

        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-5">

            <div
                class="
                    bg-red-50
                    border
                    border-red-200
                    text-red-800
                    px-4
                    py-3
                    rounded-xl
                    shadow-sm
                "
            >

                <div class="flex items-center gap-2">

                    <span class="text-lg">
                        !
                    </span>

                    <span class="text-sm font-medium">
                        {{ session('error') }}
                    </span>

                </div>

            </div>

        </div>

    @endif


    <!-- ============================================================
         VALIDATION ERRORS
    ============================================================ -->

    @if($errors->any())

        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-5">

            <div
                class="
                    bg-red-50
                    border
                    border-red-200
                    text-red-800
                    px-4
                    py-3
                    rounded-xl
                    shadow-sm
                "
            >

                <div class="font-semibold mb-2">
                    Please correct the following errors:
                </div>

                <ul class="list-disc list-inside text-sm space-y-1">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        </div>

    @endif


    <!-- ============================================================
         MAIN CONTENT
    ============================================================ -->

    <main
        class="flex-grow w-full"
    >

        <div
            class="w-full"
        >

            @yield('content')

        </div>

    </main>


    <!-- ============================================================
         FOOTER
    ============================================================ -->

    <footer
        class="
            bg-white
            border-t
            border-slate-200
            py-8
            mt-12
        "
    >

        <div
            class="
                max-w-7xl
                mx-auto
                px-4
                sm:px-6
                lg:px-8
                text-center
                text-sm
                text-slate-600
            "
        >

            <p>
                🔐 WebAuthn Authentication System
                |
                Built with Laravel & Tailwind
            </p>

        </div>

    </footer>


    <!-- ============================================================
         WEBAUTHN JAVASCRIPT HELPER
    ============================================================ -->

    <script>

        class Webauthn {

            /**
             * ======================================================
             * REGISTER A NEW WEBAUTHN CREDENTIAL
             * ======================================================
             */

            async register() {

                try {

                    // Get registration options from server

                    const optionsResponse = await fetch(
                        '/webauthn/register/options',
                        {
                            method: 'POST',

                            headers: {

                                'X-CSRF-TOKEN':
                                    document.querySelector(
                                        'meta[name="csrf-token"]'
                                    ).content,

                                'Content-Type':
                                    'application/json'

                            }
                        }
                    );


                    if (!optionsResponse.ok) {

                        throw new Error(
                            'Failed to get registration options'
                        );

                    }


                    const options =
                        await optionsResponse.json();


                    // Convert challenge

                    options.challenge =
                        this.bufferDecode(
                            options.challenge
                        );


                    // Convert user ID

                    options.user.id =
                        this.bufferDecode(
                            options.user.id
                        );


                    // Call browser WebAuthn API

                    const attestation =
                        await navigator.credentials.create(
                            {
                                publicKey: options
                            }
                        );


                    if (!attestation) {

                        throw new Error(
                            'Registration was cancelled'
                        );

                    }


                    // Send registration response

                    const registerResponse =
                        await fetch(
                            '/webauthn/register',
                            {
                                method: 'POST',

                                headers: {

                                    'X-CSRF-TOKEN':
                                        document.querySelector(
                                            'meta[name="csrf-token"]'
                                        ).content,

                                    'Content-Type':
                                        'application/json'

                                },

                                body: JSON.stringify({

                                    id:
                                        attestation.id,

                                    rawId:
                                        this.bufferEncode(
                                            attestation.rawId
                                        ),

                                    response: {

                                        attestationObject:
                                            this.bufferEncode(
                                                attestation
                                                    .response
                                                    .attestationObject
                                            ),

                                        clientDataJSON:
                                            this.bufferEncode(
                                                attestation
                                                    .response
                                                    .clientDataJSON
                                            )

                                    },

                                    type:
                                        attestation.type,

                                    deviceName:
                                        `Device ${
                                            new Date()
                                                .toLocaleDateString()
                                        }`

                                })

                            }
                        );


                    const result =
                        await registerResponse.json();


                    if (!result.success) {

                        throw new Error(
                            result.message ||
                            'Registration failed'
                        );

                    }


                    return result;

                } catch (error) {

                    console.error(
                        'WebAuthn Registration Error:',
                        error
                    );

                    throw error;

                }

            }


            /**
             * ======================================================
             * LOGIN WITH WEBAUTHN CREDENTIAL
             * ======================================================
             */

            async login(options = {}) {

                try {

                    const email =
                        options.email || '';


                    // Get login options

                    const optionsResponse =
                        await fetch(
                            '/webauthn/login/options',
                            {
                                method: 'POST',

                                headers: {

                                    'X-CSRF-TOKEN':
                                        document.querySelector(
                                            'meta[name="csrf-token"]'
                                        ).content,

                                    'Content-Type':
                                        'application/json'

                                },

                                body: JSON.stringify({
                                    email: email
                                })

                            }
                        );


                    if (!optionsResponse.ok) {

                        throw new Error(
                            'Failed to get login options'
                        );

                    }


                    const loginOptions =
                        await optionsResponse.json();


                    // Convert challenge

                    loginOptions.challenge =
                        this.bufferDecode(
                            loginOptions.challenge
                        );


                    // Convert allowed credentials

                    if (
                        loginOptions.allowCredentials &&
                        loginOptions.allowCredentials.length > 0
                    ) {

                        loginOptions.allowCredentials =
                            loginOptions.allowCredentials.map(
                                cred => ({

                                    ...cred,

                                    id:
                                        this.bufferDecode(
                                            cred.id
                                        )

                                })
                            );

                    }


                    // Call browser WebAuthn API

                    const assertion =
                        await navigator.credentials.get(
                            {
                                publicKey: loginOptions,

                                mediation: 'optional'
                            }
                        );


                    if (!assertion) {

                        throw new Error(
                            'Authentication was cancelled'
                        );

                    }


                    // Send authentication response

                    const loginResponse =
                        await fetch(
                            '/webauthn/login',
                            {
                                method: 'POST',

                                headers: {

                                    'X-CSRF-TOKEN':
                                        document.querySelector(
                                            'meta[name="csrf-token"]'
                                        ).content,

                                    'Content-Type':
                                        'application/json'

                                },

                                body: JSON.stringify({

                                    id:
                                        assertion.id,

                                    rawId:
                                        this.bufferEncode(
                                            assertion.rawId
                                        ),

                                    response: {

                                        authenticatorData:
                                            this.bufferEncode(
                                                assertion
                                                    .response
                                                    .authenticatorData
                                            ),

                                        clientDataJSON:
                                            this.bufferEncode(
                                                assertion
                                                    .response
                                                    .clientDataJSON
                                            ),

                                        signature:
                                            this.bufferEncode(
                                                assertion
                                                    .response
                                                    .signature
                                            ),

                                        userHandle:
                                            assertion
                                                .response
                                                .userHandle
                                                ? this.bufferEncode(
                                                    assertion
                                                        .response
                                                        .userHandle
                                                )
                                                : null,

                                        signCount:
                                            assertion
                                                .response
                                                .signCount

                                    },

                                    type:
                                        assertion.type

                                })

                            }
                        );


                    const result =
                        await loginResponse.json();


                    if (!result.success) {

                        throw new Error(
                            result.message ||
                            'Authentication failed'
                        );

                    }


                    // Redirect after successful login

                    if (result.redirect) {

                        window.location.href =
                            result.redirect;

                    }


                    return result;

                } catch (error) {

                    console.error(
                        'WebAuthn Login Error:',
                        error
                    );

                    throw error;

                }

            }


            /**
             * ======================================================
             * ENCODE ARRAY BUFFER TO BASE64
             * ======================================================
             */

            bufferEncode(buffer) {

                return btoa(
                    String.fromCharCode.apply(
                        null,
                        new Uint8Array(buffer)
                    )
                );

            }


            /**
             * ======================================================
             * DECODE BASE64 TO UINT8 ARRAY
             * ======================================================
             */

            bufferDecode(buffer) {

                return new Uint8Array(
                    atob(buffer)
                        .split('')
                        .map(
                            c => c.charCodeAt(0)
                        )
                );

            }


            /**
             * ======================================================
             * CHECK WEBAUTHN SUPPORT
             * ======================================================
             */

            static isSupported() {

                return (
                    window.PublicKeyCredential !== undefined &&
                    navigator.credentials !== undefined
                );

            }

        }


        // ============================================================
        // CHECK WEBAUTHN SUPPORT WHEN PAGE LOADS
        // ============================================================

        window.addEventListener(
            'DOMContentLoaded',
            () => {

                if (!Webauthn.isSupported()) {

                    console.warn(
                        'WebAuthn is not supported in this browser'
                    );

                }

            }
        );

    </script>

</body>

</html>