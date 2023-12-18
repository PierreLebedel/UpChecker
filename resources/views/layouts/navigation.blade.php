<div class="bg-primary text-primary-content">
    <div class="container navbar px-0">
        <div class="navbar-start">
            <a href="{{ route('home') }}" class="flex items-center flex-shrink-0">
                <x-application-logo class="block h-8 w-auto me-2" />
                <div class="text-2xl font-semibold">{{ config('app.name') }}</div>
            </a>

            @auth
            <div class="flex ms-10 gap-1">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-nav-link>
                <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                    {{ __('Mes projets') }}
                </x-nav-link>
            </div>
            @endauth
        </div>

        <div class="navbar-end">
            
            @auth
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost rounded-lg p-0 gap-0">
                    <div class="p-2">
                        {{ Auth::user()->name }}
                    </div>
                    <div class="w-12 rounded-lg overflow-hidden">
                        <img alt="{{ Auth::user()->name }}"  src="https://daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg" class="block" />
                    </div>
                </div>
                <ul tabindex="0" class="mt-1 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 text-base-content rounded-lg w-auto whitespace-nowrap">
                    <li>
                        <a href="{{ route('profile.edit') }}">{{ __('Profile') }}</a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" href="{{ route('logout') }}" onclick="event.preventDefault();this.closest('form').submit();">
                            @csrf
                            <a href="#">{{ __('Log Out') }}</a>
                        </form>
                    </li>
                </ul>
            </div>
            @endauth

            @guest
                @if (Route::has('login'))
                    <div class="text-end">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-sm">{{ __('Dashboard') }}</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm">{{ __('Log in') }}</a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-sm btn-secondary">{{ __('Register') }}</a>
                            @endif
                        @endauth
                    </div>
                @endif
            @endguest


        </div>
    </div>

</div>