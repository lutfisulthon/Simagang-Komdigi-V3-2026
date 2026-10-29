<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Simagang Admin - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    @include('layouts.partials.theme-head')

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-straight/css/uicons-regular-straight.css">

    <style>
        * { box-sizing: border-box; margin:0; padding:0; }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #193097;
        }

        .bg-wrapper {
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: linear-gradient(135deg, #193097 10%, #628ECB 30%, #D5DEEF 100%);
            position: relative;
            overflow: hidden;
        }

        .bg-wrapper::before,
        .bg-wrapper::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }

        .bg-wrapper::before {
            width: 400px;
            height: 400px;
            top: -120px;
            right: -120px;
        }

        .bg-wrapper::after {
            width: 300px;
            height: 300px;
            bottom: -100px;
            left: -100px;
        }

        @font-face {
            font-family: 'Etna';
            src: url('/fonts/Etna-Free-Font.otf') format('opentype');
        }

        .font-etna {
            font-family: 'Etna', sans-serif;
        }

        .login-container {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0,0,0,.2);
            padding: 48px 40px;
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
        }

        .close-login-btn {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 36px;
        height: 36px;
        color: #333333;
        font-size: 32px;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: background 0.2s ease, box-shadow 0.2s ease;
        z-index: 10;
    }

    .close-login-btn:hover {
        color: #000000;
        transform: scale(1.2);
        text-decoration: none;
    }

        .close-login-btn:active {
            transform: scale(0.95);
        }

    </style>
</head>

<body>
<div class="bg-wrapper">

    <div class="login-container">
        <a href="{{ url('/') }}" class="close-login-btn" title="Tutup">
            &times;
        </a>

        <div class="text-center mb-3 space-y-3">
            <div class="flex flex-col items-center p-4">
                <img src="{{ url('storage/vendor/logo_komdigi.png') }}" 
                        alt="Logo" 
                        class="object-contain" 
                        style="width: 80px; height: 80px"/>
                
                <h1 class="text-3xl font-extrabold font-etna">
                <span style="color: #9d272a">SI</span><span style="color: #086bb0">MA</span><span style="color: #2dabe2">GA</span><span style="color: #efc400">NG</span> </h1>

                <p class="font-etna" style="color: #626161; font-size:10px">
                    Sistem Manajemen Magang
                </p>
            </div>
        </div>

        <!-- FORM -->
         @if (session('status'))
    <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
        {{ session('status') }}
    </div>
@endif
        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf

            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-700">
                    Email
                </label>
                
                <input type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="email@contoh.com"
                        required class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition" >
                @error('email')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-700">
                    Password
                </label>
                <div class="relative">
                    <input type="password"
                        id="login_password"
                        name="password"
                        placeholder="••••••••"
                        required
                        class="w-full px-4 py-3 pr-12 rounded-xl border border-gray-300 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition">

                    <button type="button"
                            onclick="togglePasswordVisibility('login_password', 'login_eye_open', 'login_eye_closed')"
                            class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-500 hover:text-blue-600 focus:outline-none"
                            title="Lihat password">

                        {{-- Icon mata disilang: muncul saat password tertutup --}}
                        <svg id="login_eye_closed"
                            xmlns="http://www.w3.org/2000/svg"
                            width="22"
                            height="22"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.2"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M3 3l18 18"/>
                            <path d="M10.58 10.58A2 2 0 0 0 13.42 13.42"/>
                            <path d="M9.88 5.09A9.77 9.77 0 0 1 12 5c5 0 9 4.5 10 7a13.13 13.13 0 0 1-3.1 4.26"/>
                            <path d="M6.61 6.61A13.53 13.53 0 0 0 2 12c1 2.5 5 7 10 7a9.74 9.74 0 0 0 4.39-1.04"/>
                        </svg>

                        {{-- Icon mata biasa: muncul saat password terlihat --}}
                        <svg id="login_eye_open"
                            class="hidden"
                            xmlns="http://www.w3.org/2000/svg"
                            width="22"
                            height="22"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.2"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 pb-1">
    <label class="flex items-center gap-2 text-sm text-gray-600">
        <input type="checkbox"
                name="remember"
                class="w-4 h-4 rounded border-gray-300 text-blue-600">
        Ingat saya
    </label>

    <a href="{{ route('password.request') }}"
       class="text-sm text-blue-600 hover:text-blue-800 font-semibold">
        Lupa Password?
    </a>
</div>

            <button type="submit"
                class="w-full py-3 rounded-xl font-semibold text-white
                        bg-gradient-to-r from-blue-600 to-blue-700
                        hover:from-blue-700 hover:to-blue-800
                        shadow-lg shadow-blue-500/30 transition duration-200">
                Masuk
            </button>
        </form>

        <div class="text-center mt-6 text-xs text-gray-500">
            © 2026 Simagang. Sistem manajemen magang BBLSDM Komdigi Makassar.
        </div>

    </div>

</div>
<script>
    function togglePasswordVisibility(inputId, openIconId, closedIconId) {
        const input = document.getElementById(inputId);
        const openIcon = document.getElementById(openIconId);
        const closedIcon = document.getElementById(closedIconId);

        if (!input || !openIcon || !closedIcon) return;

        if (input.type === 'password') {
            input.type = 'text';

            // Password terlihat = icon mata biasa
            openIcon.classList.remove('hidden');
            closedIcon.classList.add('hidden');
        } else {
            input.type = 'password';

            // Password tertutup = icon mata disilang
            openIcon.classList.add('hidden');
            closedIcon.classList.remove('hidden');
        }
    }
</script>
</body>
</html>