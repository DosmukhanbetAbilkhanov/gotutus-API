<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LegalPage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebPageController extends Controller
{
    public function landing(): View
    {
        return view('pages.landing');
    }

    public function privacyPolicy(Request $request): View
    {
        $supportedLanguages = ['en', 'ru', 'kk'];

        // Determine language: query param > browser preference > default
        $lang = $request->query('lang');
        if (! in_array($lang, $supportedLanguages, true)) {
            $lang = $request->getPreferredLanguage($supportedLanguages) ?? 'en';
        }

        $page = LegalPage::getActive(LegalPage::SLUG_PRIVACY_POLICY);

        $title = '';
        $content = '';
        $lastUpdated = null;
        $version = '';

        if ($page) {
            $translation = $page->translations->firstWhere('language_code', $lang)
                ?? $page->translations->firstWhere('language_code', 'en');

            $title = $translation?->title ?? '';
            $content = $translation?->content ?? '';
            $lastUpdated = $page->published_at;
            $version = $page->version;
        }

        return view('pages.privacy-policy', [
            'title' => $title,
            'content' => $content,
            'lastUpdated' => $lastUpdated,
            'version' => $version,
            'currentLang' => $lang,
            'supportedLanguages' => $supportedLanguages,
        ]);
    }
}
