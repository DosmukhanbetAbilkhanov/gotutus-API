{{-- Footer --}}
<footer class="py-10 bg-gray-900 text-gray-400">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="{{ route('landing', isset($currentLang) && $currentLang !== 'en' ? ['lang' => $currentLang] : []) }}" class="flex items-center gap-2 group">
                <img src="{{ asset('images/brand/logo.png') }}" alt="Tanys" class="w-7 h-7 rounded-lg">
                <span class="text-sm font-righteous text-gray-300 group-hover:text-white transition-colors">tanys</span>
            </a>
            <div class="flex items-center gap-6 text-sm">
                <a href="{{ route('privacy-policy', isset($currentLang) && $currentLang !== 'en' ? ['lang' => $currentLang] : []) }}" class="hover:text-white transition-colors duration-200">
                    @if(isset($currentLang) && $currentLang === 'ru')
                        Политика конфиденциальности
                    @elseif(isset($currentLang) && $currentLang === 'kz')
                        Құпиялылық саясаты
                    @else
                        Privacy Policy
                    @endif
                </a>
                <a href="mailto:administrator@tanys.app" class="hover:text-white transition-colors duration-200">administrator@tanys.app</a>
            </div>
        </div>
        <div class="mt-6 pt-6 border-t border-gray-800 text-center text-xs text-gray-500">
            @if(isset($currentLang) && $currentLang === 'ru')
                &copy; {{ date('Y') }} Tanys. ТОО AMIR TRADE (Amir Trade LLP). Все права защищены.
            @elseif(isset($currentLang) && $currentLang === 'kz')
                &copy; {{ date('Y') }} Tanys. AMIR TRADE ЖШС (Amir Trade LLP). Барлық құқықтар қорғалған.
            @else
                &copy; {{ date('Y') }} Tanys. TOO AMIR TRADE (Amir Trade LLP). All rights reserved.
            @endif
        </div>
    </div>
</footer>
