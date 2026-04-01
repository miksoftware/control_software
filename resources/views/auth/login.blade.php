<x-guest-layout>
    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[500px]">
        <!-- Brand Side -->
        <div class="w-full md:w-1/2 bg-primary p-12 flex flex-col justify-center items-center text-white text-center">
            <div class="text-4xl font-extrabold tracking-tighter mb-4">
                MIK<span class="text-accent">SOFTWARE</span>
            </div>
            <p class="text-white/70 text-lg max-w-xs">
                Soluciones integrales para la gestión de licencias, proyectos y clientes.
            </p>
            <div class="mt-12 w-24 h-1 bg-accent rounded-full"></div>
        </div>

        <!-- Form Side -->
        <div class="w-full md:w-1/2 p-12 flex flex-col justify-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Bienvenido</h2>
            <p class="text-gray-500 mb-8">Inicia sesión en tu cuenta</p>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-accent focus:border-accent transition duration-150 ease-in-out sm:text-sm"
                            placeholder="usuario@miksoftware.com">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input id="password" type="password" name="password" required
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-accent focus:border-accent transition duration-150 ease-in-out sm:text-sm"
                            placeholder="••••••••">
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-600">Recuérdame</label>
                    </div>

                    @if (Route::has('password.request'))
                        <a class="text-sm font-medium text-accent hover:text-accent/80 transition-colors" href="{{ route('password.request') }}">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-accent hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition duration-150 ease-in-out">
                        Iniciar Sesión
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>