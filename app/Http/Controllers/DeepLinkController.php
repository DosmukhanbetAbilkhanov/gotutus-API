<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HangoutRequestStatus;
use App\Models\HangoutRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeepLinkController extends Controller
{
    private const SUPPORTED_LANGUAGES = ['en', 'ru', 'kz'];

    /**
     * Android App Links verification file.
     * Served at /.well-known/assetlinks.json as application/json.
     */
    public function assetLinks(): JsonResponse
    {
        $payload = [[
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => [
                'namespace' => 'android_app',
                'package_name' => config('deeplink.android.package'),
                'sha256_cert_fingerprints' => config('deeplink.android.sha256_fingerprints', []),
            ],
        ]];

        return response()->json($payload);
    }

    /**
     * iOS Universal Links verification file.
     * Served at /.well-known/apple-app-site-association as application/json,
     * with NO file extension and NO redirect.
     */
    public function appleAppSiteAssociation(): JsonResponse
    {
        $appId = trim((string) config('deeplink.ios.team_id')).'.'.config('deeplink.ios.bundle_id');

        $payload = [
            'applinks' => [
                'details' => [[
                    'appIDs' => [$appId],
                    'components' => [[
                        '/' => config('deeplink.hangout_path').'/*',
                        'comment' => 'Hangout deep links',
                    ]],
                ]],
            ],
        ];

        return response()->json($payload);
    }

    /**
     * Public hangout landing page: opens the app when installed (via the
     * association files), otherwise shows a shareable preview + store buttons.
     */
    public function hangout(Request $request, int $id): View|Response
    {
        $lang = $this->resolveLanguage($request);
        app()->setLocale($lang === 'kz' ? 'kk' : $lang);

        $hangout = HangoutRequest::query()
            ->with(['user', 'city.translations', 'activityType.translations', 'place.translations'])
            ->find($id);

        $unavailable = $hangout === null
            || $hangout->status === HangoutRequestStatus::Cancelled;

        if ($unavailable) {
            return response()->view('pages.hangout', [
                'lang' => $lang,
                'currentLang' => $lang,
                'supportedLanguages' => self::SUPPORTED_LANGUAGES,
                'available' => false,
                'hangout' => null,
                'og' => $this->defaultOg(),
                'androidStoreUrl' => config('deeplink.android.store_url'),
                'iosStoreUrl' => config('deeplink.ios.store_url'),
                'appUrl' => $request->fullUrl(),
            ], $hangout === null ? 404 : 410);
        }

        $activity = $hangout->activityType?->name;
        $place = $hangout->place?->name;
        $city = $hangout->city?->name;
        $when = $this->formatWhen($hangout);

        $title = trim(($activity ?: __('Hangout')).($place ? ' @ '.$place : ''));
        $descParts = array_filter([$when, $city]);
        $description = implode(' · ', $descParts);

        return view('pages.hangout', [
            'lang' => $lang,
            'currentLang' => $lang,
            'supportedLanguages' => self::SUPPORTED_LANGUAGES,
            'available' => true,
            'hangout' => $hangout,
            'activity' => $activity,
            'place' => $place,
            'city' => $city,
            'when' => $when,
            'og' => [
                'title' => $title.' — Tanys',
                'description' => $description ?: 'Join this hangout on Tanys',
                'image' => $this->resolveImage($hangout) ?? $this->defaultOg()['image'],
            ],
            'androidStoreUrl' => config('deeplink.android.store_url'),
            'iosStoreUrl' => config('deeplink.ios.store_url'),
            'appUrl' => $request->fullUrl(),
        ]);
    }

    private function formatWhen(HangoutRequest $hangout): string
    {
        $date = $hangout->date?->isoFormat('ddd, D MMM');
        $time = $hangout->time?->format('H:i');

        return trim(implode(' ', array_filter([$date, $time])));
    }

    private function resolveImage(HangoutRequest $hangout): ?string
    {
        $path = $hangout->activityType?->bg_photo;
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return str_starts_with($path, '/')
            ? url($path)
            : url('storage/'.ltrim($path, '/'));
    }

    /**
     * @return array{title: string, description: string, image: string}
     */
    private function defaultOg(): array
    {
        return [
            'title' => 'Tanys — Find Your Perfect Hangout Partner',
            'description' => 'Create meetups, join activities, and meet new friends in your city.',
            'image' => url('/og-default.png'),
        ];
    }

    private function resolveLanguage(Request $request): string
    {
        $lang = $request->query('lang');

        if (in_array($lang, self::SUPPORTED_LANGUAGES, true)) {
            return $lang;
        }

        return $request->getPreferredLanguage(self::SUPPORTED_LANGUAGES) ?? 'en';
    }
}
