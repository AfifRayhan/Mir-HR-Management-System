<x-guest-layout>
    <div class="card shadow-sm border-0 auth-card">
        <div class="card-body p-4 text-center">
            <h1 class="h4 fw-semibold mb-1">
                {{ __('Create an account') }}
            </h1>
            <p class="small text-muted mb-4">
                {{ __('Fill in your details to get started.') }}
            </p>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-3">
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input
                        id="name"
                        type="text"
                        name="name"
                        :value="old('name')"
                        class="form-control"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

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
                        autocomplete="username"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control"
                        required
                        autocomplete="new-password"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="mb-3">
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="form-control"
                        required
                        autocomplete="new-password"
                    />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="row justify-content-center mb-2 mt-2">
                    <button type="submit" class="btn btn-dark">
                        {{ __('Register') }}
                    </button>
                </div>

                <div class="row justify-content-center">
                    <p class="small text-muted mb-0">
                        {{ __('Already registered?') }}
                        <a href="{{ route('login') }}" class="text-decoration-underline text-dark">
                            {{ __('Log in') }}
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
