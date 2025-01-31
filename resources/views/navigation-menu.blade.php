<nav x-data="{ menuOpen: false }" class="bg-white">
    <div class="border-b border-gray-200">
        <div class="max-w-7xl mx-auto p-4 sm:px-6">
            <div class="flex justify-between space-x-8">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <div class="hidden sm:flex sm:w-full sm:items-center sm:justify-stretch sm:space-x-4">
                    <form method="GET" action="{{ route('products.search')}}" class="w-full">
                        <x-input name="query" value="{{ request()->get('query') }}" class="!rounded-full !w-full text-sm py-3" placeholder="{{ __('Search by product name, SKU or paste the store URL') }}" />
                    </form>

                    @if (Auth::user())
                        <div>
                            @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                                <div class="relative">
                                    <x-dropdown align="right" width="60">
                                        <x-slot name="trigger">
                                            <span class="inline-flex rounded-md">
                                                <button aria-label="Top menu" type="button" class="inline-flex items-center px-3 py-3 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                    {{ Auth::user()->currentTeam->name }}

                                                    <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </x-slot>

                                        <x-slot name="content">
                                            <div class="w-60">
                                                <!-- Team Management -->
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    {{ __('Manage Team') }}
                                                </div>

                                                <!-- Team Settings -->
                                                <x-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                                                    {{ __('Team Settings') }}
                                                </x-dropdown-link>

                                                @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                                    <x-dropdown-link href="{{ route('teams.create') }}">
                                                        {{ __('Create New Team') }}
                                                    </x-dropdown-link>
                                                @endcan

                                                <!-- Team Switcher -->
                                                @if (Auth::user()->allTeams()->count() > 1)
                                                    <div class="border-t border-gray-200"></div>

                                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                                        {{ __('Switch Teams') }}
                                                    </div>

                                                    @foreach (Auth::user()->allTeams() as $team)
                                                        <x-switchable-team :team="$team" />
                                                    @endforeach
                                                @endif
                                            </div>
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                            @endif

                            <!-- Settings Dropdown -->
                            <div class="relative">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                            <button aria-label="User profile" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                                <img class="size-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                            </button>
                                        @else
                                            <span class="inline-flex rounded-md">
                                                <button aria-label="User profile" type="button" class="inline-flex items-center px-3 py-4 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                    <div class="truncate whitespace-nowrap max-w-24 lg:max-w-32">{{ Auth::user()->name }}</div>

                                                    <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </button>
                                            </span>
                                        @endif
                                    </x-slot>

                                    <x-slot name="content">
                                        <!-- Account Management -->
                                        <x-dropdown-link href="{{ route('user.products') }}">
                                            {{ __('Tracked Products') }}
                                        </x-dropdown-link>

                                        <x-dropdown-link href="{{ route('profile.show') }}">
                                            {{ __('Edit Profile') }}
                                        </x-dropdown-link>

                                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                            <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                                {{ __('API Tokens') }}
                                            </x-dropdown-link>
                                        @endif

                                        <!-- Authentication -->
                                        <form method="POST" action="{{ route('logout') }}" x-data>
                                            @csrf

                                            <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                                                {{ __('Log Out') }}
                                            </x-dropdown-link>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        </div>
                    @else
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <a href="/login"
                                class="text-sm text-gray-600 hover:text-gray-900 flex items-center whitespace-nowrap"  
                                x-on:click.prevent="$dispatch('show-login-modal')"
                            >
                                <span>{{ __('Start Tracking!') }}</span>
                                <i class="fa-solid fa-arrow-right pl-2"></i>
                            </a>
                        </div>
                    @endif
                </div>

                <div class="-me-2 flex items-center sm:hidden">
                    <button aria-label="Top menu" x-on:click="menuOpen = ! menuOpen" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': menuOpen, 'inline-flex': ! menuOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! menuOpen, 'inline-flex': menuOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="sm:hidden mt-4">
                <form method="GET" action="{{ route('products.search')}}" class="w-full">
                    <x-input name="query" value="{{ request()->get('query') }}" class="!rounded-full !w-full text-sm py-3" placeholder="{{ __('Search by product name, SKU or paste the store URL') }}" />
                </form>
            </div>
        </div>
    </div>

    
    <div class="border-b border-gray-200 hidden sm:block">
        <div class="max-w-7xl mx-auto">
            <div class="sm:flex px-4 sm:px-6">
                <x-catalog-menu />
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': menuOpen, 'hidden': ! menuOpen}" class="hidden sm:hidden">
        <div class="px-2 py-4 space-y-1 border-b border-gray-200">
            <x-responsive-nav-link href="{{ route('home') }}">
                {{ __('Trending') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('categories.index') }}">
                {{ __('Categories') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('stores.index') }}">
                {{ __('Stores') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        @if (Auth::user())
            <div class="px-2 py-4 border-b border-gray-200">
                <div class="flex items-center mt-2 px-3">
                    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                        <div class="shrink-0 me-3">
                            <img class="size-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                        </div>
                    @endif

                    <div>
                        <div class="font-medium text-base text-gray-600">{{ Auth::user()->name }}</div>
                        <div class="font-medium text-sm text-gray-600/40">{{ Auth::user()->email }}</div>
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <!-- Account Management -->
                    <x-responsive-nav-link href="{{ route('user.products') }}">
                        {{ __('Tracked Products') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link href="{{ route('profile.show') }}">
                        {{ __('Edit Profile') }}
                    </x-responsive-nav-link>

                    @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                        <x-responsive-nav-link href="{{ route('api-tokens.index') }}">
                            {{ __('API Tokens') }}
                        </x-responsive-nav-link>
                    @endif

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf

                        <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>

                    <!-- Team Management -->
                    @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                        <div class="border-t border-gray-200"></div>

                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Manage Team') }}
                        </div>

                        <!-- Team Settings -->
                        <x-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" :active="request()->routeIs('teams.show')">
                            {{ __('Team Settings') }}
                        </x-responsive-nav-link>

                        @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                            <x-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                                {{ __('Create New Team') }}
                            </x-responsive-nav-link>
                        @endcan

                        <!-- Team Switcher -->
                        @if (Auth::user()->allTeams()->count() > 1)
                            <div class="border-t border-gray-200"></div>

                            <div class="block px-4 py-2 text-xs text-gray-400">
                                {{ __('Switch Teams') }}
                            </div>

                            @foreach (Auth::user()->allTeams() as $team)
                                <x-switchable-team :team="$team" component="responsive-nav-link" />
                            @endforeach
                        @endif
                    @endif
                </div>
            </div>
        @else
            <div class="px-2 py-4 space-y-1 border-b border-gray-200">
                <x-responsive-nav-link href="{{ route('login') }}">
                    {{ __('Login') }}
                </x-responsive-nav-link>
            </div>
        @endif
    </div>
</nav>
