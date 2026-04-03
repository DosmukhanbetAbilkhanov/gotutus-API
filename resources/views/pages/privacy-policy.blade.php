@extends('layouts.web')

@section('title', $title . ' — Tanys')
@section('meta_description', 'Privacy Policy for the Tanys mobile application.')
@section('og_title', $title . ' — Tanys')
@section('og_description', 'Privacy Policy for the Tanys mobile application.')

@section('content')

    @include('partials.navbar')

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
                                @elseif($currentLang === 'kz')
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
                        @elseif($currentLang === 'kz')
                            Құпиялылық саясаты әзірше қолжетімсіз.
                        @else
                            Privacy policy is not available yet.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </main>

    @include('partials.footer')

@endsection
