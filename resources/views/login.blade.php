<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login</title>
        <link rel="icon" href="{{ asset('favicon.png') }}">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        @vite(['resources/css/login.css'])
    </head>
    <body>
        <!-- <div class="login-container">
            <div class="design-container">
                <div class="logo-with-dining-center-text">
                    <img src="{{ asset('photos/UMDiningcenter.png') }}" alt="UM Dining Center" class ="login-logoDiningCenter">
                </div>
                <hr>
            </div>
            <div class="login-form-container">
                @if ($errors->any())
                    <div class="alert alert-danger text-center">
                        {{ $errors->first() }}
                    </div>
                @endif
                <form class="login-form" method="POST" action="{{ route('login.submit') }}">
                    @csrf
                    <div class="input-wrapper">
                        <i class="fa-regular fa-user"></i>
                        <input class="login-inputs" type="email" name="email" placeholder="email" autocomplete="off" required>
                    </div>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input class="login-inputs" type="password" name="password" placeholder="password" required>
                    </div>
                    <button class="login-button">Login</button>
                </form>
            </div>
        </div> -->
    </body>
</html>