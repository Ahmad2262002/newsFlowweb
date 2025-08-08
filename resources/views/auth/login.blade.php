@if(session('success'))
    <div class="mb-4 font-medium text-sm text-green-600">
        {{ session('success') }}
    </div>
@endif

<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form id="loginForm" method="POST">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3" type="submit">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    @push('scripts')
    <!-- Load SweetAlert2 and Axios -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const remember = document.getElementById('remember_me').checked;
    
    submitButton.disabled = true;
    submitButton.innerHTML = 'Logging in...';

    try {
        const response = await axios.post('/api/login', {
            email: email,
            password: password,
            remember: remember
        }, {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (response.data.token) {
            // Store token in multiple storage mechanisms
            localStorage.setItem('auth_token', response.data.token);
            sessionStorage.setItem('auth_token', response.data.token);
            document.cookie = `auth_token=${response.data.token}; path=/; max-age=${60 * 60 * 24 * 7}; Secure; SameSite=Lax`;
            
            // Set default headers for Axios
            axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
            
            // Force a hard redirect to clear any state issues
            window.location.assign(response.data.role_id === 3 
                ? '/employee/dashboard' 
                : '/admin/dashboard');
        } else {
            throw new Error('Login failed - no token received');
        }
    } catch (error) {
        submitButton.disabled = false;
        submitButton.innerHTML = 'Log in';
        
        // Clear any potentially invalid tokens
        localStorage.removeItem('auth_token');
        sessionStorage.removeItem('auth_token');
        
        let errorMessage = 'Invalid credentials';
        if (error.response) {
            errorMessage = error.response.data?.message || 
                         error.response.data?.error ||
                         Object.values(error.response.data?.errors || {}).join('<br>') || 
                         errorMessage;
        }
        
        await Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            html: errorMessage
        });
    }
});
    </script>
    @endpush
</x-guest-layout>