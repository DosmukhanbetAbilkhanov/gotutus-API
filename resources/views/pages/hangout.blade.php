@extends('layouts.web')

@section('title', $og['title'])
@section('meta_description', $og['description'])
@section('og_title', $og['title'])
@section('og_description', $og['description'])
@section('og_image', $og['image'])

@section('content')
<main class="min-h-screen flex items-center justify-center px-4 py-16 bg-gradient-to-b from-white to-gray-50">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-gray-100 p-8 text-center">
        @if (! $available)
            <div class="text-5xl mb-4">🗓️</div>
            <h1 class="text-xl font-bold text-gray-900 mb-2">
                {{ __('This hangout isn’t available') }}
            </h1>
            <p class="text-gray-500 mb-8">
                {{ __('It may have been cancelled or removed. Discover other hangouts in the Tanys app.') }}
            </p>
        @else
            @if ($hangout->activityType?->icon)
                <div class="text-5xl mb-4">{{ $hangout->activityType->icon }}</div>
            @endif

            <h1 class="text-2xl font-bold text-gray-900 mb-1">
                {{ $activity ?? __('Hangout') }}@if ($place) <span class="text-gray-400">@</span> {{ $place }}@endif
            </h1>

            <div class="flex flex-wrap justify-center gap-2 my-4 text-sm">
                @if ($when)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-gray-100 text-gray-700">🕒 {{ $when }}</span>
                @endif
                @if ($city)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-gray-100 text-gray-700">📍 {{ $city }}</span>
                @endif
            </div>

            @if ($hangout->notes)
                <p class="text-gray-600 mb-6">{{ \Illuminate\Support\Str::limit($hangout->notes, 160) }}</p>
            @endif

            <a href="{{ $appUrl }}"
               class="block w-full py-3 mb-6 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition">
                {{ __('Open in Tanys') }}
            </a>
        @endif

        <p class="text-sm text-gray-400 mb-4">{{ __('Don’t have the app yet?') }}</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ $iosStoreUrl }}" class="flex-1 py-3 rounded-xl bg-black text-white text-sm font-medium hover:opacity-90 transition">App Store</a>
            <a href="{{ $androidStoreUrl }}" class="flex-1 py-3 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:opacity-90 transition">Google Play</a>
        </div>
    </div>
</main>
@endsection
