@extends('layouts.web')

@section('title', 'Tanys — Find Your Perfect Hangout Partner')
@section('meta_description', 'Tanys helps you find people to hang out with. Create meetups, join activities, and meet new friends in your city.')

@section('content')

    @include('partials.navbar')

    {{-- Hero Section --}}
    <section class="relative pt-32 pb-20 sm:pt-40 sm:pb-28 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-light via-white to-secondary-light opacity-60"></div>
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight tracking-tight">
                Find Your Perfect<br>
                <span class="text-primary">Hangout Partner</span>
            </h1>
            <p class="mt-6 text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                Create spontaneous meetups, join activities near you, and meet new people who share your interests. Coffee, walks, bowling, karaoke — anything goes.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                {{-- App Store Button --}}
                <a href="#download" class="inline-flex items-center gap-3 px-6 py-3 bg-gray-900 text-white rounded-xl hover:bg-gray-800 transition-colors duration-200 shadow-lg shadow-gray-900/20">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                    </svg>
                    <div class="text-left">
                        <div class="text-[10px] uppercase tracking-wider opacity-80">Download on the</div>
                        <div class="text-base font-semibold -mt-0.5">App Store</div>
                    </div>
                </a>
                {{-- Google Play Button --}}
                <a href="#download" class="inline-flex items-center gap-3 px-6 py-3 bg-gray-900 text-white rounded-xl hover:bg-gray-800 transition-colors duration-200 shadow-lg shadow-gray-900/20">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 01-.61-.92V2.734a1 1 0 01.609-.92zm10.89 10.893l2.302 2.302-10.937 6.333 8.635-8.635zm3.199-3.199l2.302 2.302c.63.354.63 1.026 0 1.38l-2.302 2.302-2.533-2.533 2.533-2.451zM5.864 2.658L16.8 9.075l-2.302 2.302L5.864 2.658z"/>
                    </svg>
                    <div class="text-left">
                        <div class="text-[10px] uppercase tracking-wider opacity-80">Get it on</div>
                        <div class="text-base font-semibold -mt-0.5">Google Play</div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- QR Codes Section --}}
    <section id="download" class="py-16 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-center text-gray-900">Scan to Download</h2>
            <p class="mt-3 text-center text-gray-500">Point your camera at the QR code to get the app</p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-10">
                {{-- App Store QR --}}
                <div class="text-center">
                    <div class="w-48 h-48 border-2 border-dashed border-gray-300 rounded-2xl flex items-center justify-center bg-white shadow-sm">
                        <span class="text-sm text-gray-400 font-medium px-4">QR Code<br>App Store</span>
                    </div>
                    <p class="mt-3 text-sm font-medium text-gray-600">App Store</p>
                </div>
                {{-- Google Play QR --}}
                <div class="text-center">
                    <div class="w-48 h-48 border-2 border-dashed border-gray-300 rounded-2xl flex items-center justify-center bg-white shadow-sm">
                        <span class="text-sm text-gray-400 font-medium px-4">QR Code<br>Google Play</span>
                    </div>
                    <p class="mt-3 text-sm font-medium text-gray-600">Google Play</p>
                </div>
            </div>
        </div>
    </section>

    {{-- How It Works Section --}}
    <section class="py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-center text-gray-900">How It Works</h2>
            <p class="mt-3 text-center text-gray-500 max-w-xl mx-auto">Three simple steps to start meeting new people</p>
            <div class="mt-14 grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Step 1 --}}
                <div class="relative bg-white border border-gray-100 rounded-2xl p-8 text-center shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-primary-light rounded-xl mb-5">
                        <svg class="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Create a Hangout</h3>
                    <p class="mt-3 text-gray-500 leading-relaxed">Pick an activity, set the time and place, and your hangout is live for others to discover.</p>
                </div>
                {{-- Step 2 --}}
                <div class="relative bg-white border border-gray-100 rounded-2xl p-8 text-center shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-secondary-light rounded-xl mb-5">
                        <svg class="w-7 h-7 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Find Companions</h3>
                    <p class="mt-3 text-gray-500 leading-relaxed">Browse hangouts in your city, filter by activity, and send a join request to the host.</p>
                </div>
                {{-- Step 3 --}}
                <div class="relative bg-white border border-gray-100 rounded-2xl p-8 text-center shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-primary-light rounded-xl mb-5">
                        <svg class="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Enjoy Together</h3>
                    <p class="mt-3 text-gray-500 leading-relaxed">Chat with your match, plan the details, and enjoy a great time together in real life.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Screenshots Section --}}
    <section class="py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-center text-gray-900">See It in Action</h2>
            <p class="mt-3 text-center text-gray-500">A glimpse of the Tanys experience</p>
            <div class="mt-12 flex gap-6 overflow-x-auto pb-6 snap-x snap-mandatory scrollbar-hide">
                @foreach(['Feed', 'Hangout Detail', 'Chat', 'Profile', 'Create Hangout'] as $label)
                    <div class="flex-none snap-center">
                        <div class="w-56 h-[480px] bg-white border-2 border-dashed border-gray-200 rounded-[2rem] flex items-center justify-center shadow-sm">
                            <span class="text-sm text-gray-400 font-medium">{{ $label }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Activities Section --}}
    <section class="py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Activities for Every Mood</h2>
            <p class="mt-3 text-gray-500 max-w-xl mx-auto">Whether you're into coffee, sports, or nightlife — there's always someone who wants to join</p>
            <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                @foreach([
                    ['icon' => "\u{2615}", 'label' => 'Coffee'],
                    ['icon' => "\u{1F37A}", 'label' => 'Beer'],
                    ['icon' => "\u{1F3B3}", 'label' => 'Bowling'],
                    ['icon' => "\u{1F3A4}", 'label' => 'Karaoke'],
                    ['icon' => "\u{1F6B6}", 'label' => 'Walk'],
                    ['icon' => "\u{1F3AC}", 'label' => 'Cinema'],
                    ['icon' => "\u{1F3B1}", 'label' => 'Billiards'],
                    ['icon' => "\u{1F37D}\u{FE0F}", 'label' => 'Restaurant'],
                    ['icon' => "\u{1F9D6}", 'label' => 'Bathhouse'],
                    ['icon' => "\u{1F3B2}", 'label' => 'Board Games'],
                    ['icon' => "\u{1F4A8}", 'label' => 'Hookah'],
                    ['icon' => "\u{26BD}", 'label' => 'Football'],
                ] as $activity)
                    <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-full text-sm font-medium text-gray-700">
                        <span class="text-lg">{{ $activity['icon'] }}</span>
                        {{ $activity['label'] }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-20 bg-primary">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-white">Ready to Find Your Hangout Partner?</h2>
            <p class="mt-4 text-primary-light text-lg opacity-90">Download the app and start meeting new people in your city today.</p>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="#download" class="inline-flex items-center gap-3 px-6 py-3 bg-white text-gray-900 rounded-xl hover:bg-gray-50 transition-colors duration-200 shadow-lg font-semibold">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                    </svg>
                    App Store
                </a>
                <a href="#download" class="inline-flex items-center gap-3 px-6 py-3 bg-white text-gray-900 rounded-xl hover:bg-gray-50 transition-colors duration-200 shadow-lg font-semibold">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 01-.61-.92V2.734a1 1 0 01.609-.92zm10.89 10.893l2.302 2.302-10.937 6.333 8.635-8.635zm3.199-3.199l2.302 2.302c.63.354.63 1.026 0 1.38l-2.302 2.302-2.533-2.533 2.533-2.451zM5.864 2.658L16.8 9.075l-2.302 2.302L5.864 2.658z"/>
                    </svg>
                    Google Play
                </a>
            </div>
        </div>
    </section>

    @include('partials.footer')

@push('styles')
<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@endsection
