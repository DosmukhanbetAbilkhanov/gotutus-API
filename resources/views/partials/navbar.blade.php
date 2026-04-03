{{-- Navigation Bar --}}
<nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('landing', isset($currentLang) && $currentLang !== 'en' ? ['lang' => $currentLang] : []) }}" class="flex items-center gap-2.5 group">
                <img src="{{ asset('images/brand/logo.png') }}" alt="Tanys" class="w-9 h-9 rounded-xl">
                <span class="text-xl font-righteous text-gray-900 group-hover:text-primary transition-colors">tanys</span>
            </a>

            <div class="flex items-center gap-3">
                @if(isset($supportedLanguages) && isset($currentLang))
                    <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                        @foreach($supportedLanguages as $code)
                            <a href="{{ url()->current() . '?' . http_build_query(array_merge(request()->except('lang'), ['lang' => $code])) }}"
                               class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all duration-200
                                      {{ $currentLang === $code
                                          ? 'bg-white text-primary shadow-sm'
                                          : 'text-gray-500 hover:text-gray-700' }}">
                                {{ strtoupper($code) }}
                            </a>
                        @endforeach
                    </div>
                @endif

                @hasSection('navbar-right')
                    @yield('navbar-right')
                @else
                    <a href="#download" class="hidden sm:inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-dark transition-colors duration-200">
                        @if(isset($currentLang) && $currentLang === 'ru')
                            Скачать
                        @elseif(isset($currentLang) && $currentLang === 'kz')
                            Жүктеу
                        @else
                            Download
                        @endif
                    </a>
                @endif
            </div>
        </div>
    </div>
</nav>
