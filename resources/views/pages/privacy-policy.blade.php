@extends('layouts.web')

@section('title', $title . ' — Tanys')
@section('meta_description', 'Privacy Policy for the Tanys mobile application.')
@section('og_title', $title . ' — Tanys')
@section('og_description', 'Privacy Policy for the Tanys mobile application.')

@section('content')

    {{-- Navigation --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('landing') }}" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900 group-hover:text-primary transition-colors">Tanys</span>
                </a>

                {{-- Language Switcher --}}
                <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                    @foreach($supportedLanguages as $code)
                        <a href="{{ route('privacy-policy', ['lang' => $code]) }}"
                           class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all duration-200
                                  {{ $currentLang === $code
                                      ? 'bg-white text-primary shadow-sm'
                                      : 'text-gray-500 hover:text-gray-700' }}">
                            {{ strtoupper($code) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </nav>

    {{-- Content --}}
    <main class="pt-28 pb-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-10">
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">{{ $title }}</h1>
                @if($lastUpdated || $version)
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-gray-500">
                        @if($lastUpdated)
                            <span>
                                @if($currentLang === 'ru')
                                    Последнее обновление: {{ $lastUpdated->format('d.m.Y') }}
                                @elseif($currentLang === 'kk')
                                    Соңғы жаңарту: {{ $lastUpdated->format('d.m.Y') }}
                                @else
                                    Last updated: {{ $lastUpdated->format('F j, Y') }}
                                @endif
                            </span>
                        @endif
                        @if($version)
                            <span class="inline-flex items-center px-2 py-0.5 bg-gray-100 rounded text-xs font-medium text-gray-600">
                                v{{ $version }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Privacy Policy Content --}}
            @if($content)
                <article class="prose prose-gray prose-lg max-w-none
                    prose-headings:font-bold prose-headings:text-gray-900
                    prose-h2:text-xl prose-h2:mt-10 prose-h2:mb-4 prose-h2:pt-6 prose-h2:border-t prose-h2:border-gray-100
                    prose-h3:text-lg prose-h3:mt-6 prose-h3:mb-3
                    prose-p:text-gray-600 prose-p:leading-relaxed
                    prose-li:text-gray-600
                    prose-a:text-primary prose-a:no-underline hover:prose-a:underline
                    prose-strong:text-gray-800">
                    {!! $content !!}
                </article>
            @else
                <div class="text-center py-20">
                    <p class="text-gray-400 text-lg">
                        @if($currentLang === 'ru')
                            Политика конфиденциальности пока недоступна.
                        @elseif($currentLang === 'kk')
                            Құпиялылық саясаты әзірше қолжетімсіз.
                        @else
                            Privacy policy is not available yet.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </main>

    {{-- Footer --}}
    <footer class="py-8 bg-gray-50 border-t border-gray-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500">
                <a href="{{ route('landing') }}" class="hover:text-primary transition-colors duration-200">
                    &larr;
                    @if($currentLang === 'ru')
                        На главную
                    @elseif($currentLang === 'kk')
                        Басты бетке
                    @else
                        Back to home
                    @endif
                </a>
                <span>&copy; {{ date('Y') }} Tanys</span>
            </div>
        </div>
    </footer>

@endsection
