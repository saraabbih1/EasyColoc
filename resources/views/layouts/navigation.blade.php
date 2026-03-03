<nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="text-lg font-semibold text-gray-900">EasyColoc</a>

                <div class="hidden md:flex items-center gap-6 text-sm font-medium">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'text-indigo-700' : 'text-gray-700 hover:text-indigo-700' }}">
                        Dashboard
                    </a>

                    @php
                        $activeMembership = auth()->user()->activeMembership();
                        $colocationLink = $activeMembership
                            ? route('colocations.show', $activeMembership->colocation)
                            : route('colocations.create');
                    @endphp
                    <a href="{{ $colocationLink }}" class="{{ request()->routeIs('colocations.*') ? 'text-indigo-700' : 'text-gray-700 hover:text-indigo-700' }}">
                        Colocations
                    </a>

                    @if(auth()->user()->isGlobalAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'text-indigo-700' : 'text-gray-700 hover:text-indigo-700' }}">
                            Admin
                        </a>
                    @endif
                </div>
            </div>

            <div class="hidden md:flex items-center gap-4 text-sm font-medium">
                <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'text-indigo-700' : 'text-gray-700 hover:text-indigo-700' }}">
                    Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-700 hover:text-indigo-700">Logout</button>
                </form>
            </div>

            <button @click="open = !open" class="md:hidden inline-flex items-center justify-center rounded-md p-2 text-gray-700 hover:bg-gray-100">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <div x-show="open" x-transition class="md:hidden border-t border-gray-200">
        <div class="space-y-1 px-4 py-3 text-sm font-medium">
            <a href="{{ route('dashboard') }}" class="block {{ request()->routeIs('dashboard') ? 'text-indigo-700' : 'text-gray-700' }}">Dashboard</a>
            <a href="{{ $colocationLink }}" class="block {{ request()->routeIs('colocations.*') ? 'text-indigo-700' : 'text-gray-700' }}">Colocations</a>
            @if(auth()->user()->isGlobalAdmin())
                <a href="{{ route('admin.dashboard') }}" class="block {{ request()->routeIs('admin.*') ? 'text-indigo-700' : 'text-gray-700' }}">Admin</a>
            @endif
            <a href="{{ route('profile.edit') }}" class="block {{ request()->routeIs('profile.*') ? 'text-indigo-700' : 'text-gray-700' }}">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full text-left text-gray-700">Logout</button>
            </form>
        </div>
    </div>
</nav>
