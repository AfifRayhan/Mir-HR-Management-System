<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-3" :status="session('status')" />

    <div class="card shadow-sm border-0 auth-card">
        <div class="row g-0">
            <!-- Left branding column -->
            <div class="col-md-5 d-none d-md-flex flex-column justify-content-center align-items-center bg-success text-white p-4">
                <div class="text-center">
                    <div class="rounded-circle bg-white bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3 auth-logo-container">
                        <img
                            src="{{ asset('images/finallogo.png') }}"
                            alt="{{ config('app.name', 'Mir HR Management System') }} logo"
                            class="img-fluid rounded-circle auth-logo-img">
                    </div>
                    <h2 class="h5 fw-semibold mb-2">
                        {{ 'Mir HR Management System' }}
                    </h2>

                </div>
            </div>

            <!-- Right login form column -->
            <div class="col-md-7">
                <div class="card-body p-4">
                    <h1 class="h4 fw-semibold mb-1 text-center">
                        {{ __('Log in') }}
                    </h1>
                    <p class="small text-muted mb-4 text-center">
                        {{ __('Enter your credentials to continue.') }}
                    </p>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email Address -->
                        <div class="mb-3">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input
                                id="email"
                                type="email"
                                name="email"
                                :value="old('email')"
                                class="form-control"
                                required
                                autofocus
                                autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <x-input-label for="password" :value="__('Password')" />

                                @if (Route::has('password.request'))
                                <a
                                    class="small text-decoration-underline text-muted"
                                    href="{{ route('password.request') }}">
                                    {{ __('Forgot your password?') }}
                                </a>
                                @endif
                            </div>

                            <x-text-input
                                id="password"
                                type="password"
                                name="password"
                                class="form-control"
                                required
                                autocomplete="current-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-3 form-check text-start">
                            <input
                                id="remember_me"
                                type="checkbox"
                                class="form-check-input"
                                name="remember">
                            <label class="form-check-label small" for="remember_me">
                                {{ __('Remember me') }}
                            </label>
                        </div>

                        <div class="row justify-content-center mb-2">
                            <button type="submit" class="btn">
                                {{ __('Log in') }}
                            </button>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>