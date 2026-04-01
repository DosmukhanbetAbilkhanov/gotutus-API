{{-- Navigation Bar --}}
<nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
                <img src="{{ asset('storage/brand/logo.png') }}" alt="Tanys" class="w-9 h-9 rounded-xl">
                <span class="text-xl font-bold text-gray-900 group-hover:text-primary transition-colors">Tanys</span>
            </a>

            <div class="flex items-center gap-3">
                @hasSection('navbar-right')
                    @yield('navbar-right')
                @else
                    <a href="#download" class="hidden sm:inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-dark transition-colors duration-200">
                        Download
                    </a>
                @endif
            </div>
        </div>
    </div>
</nav>
