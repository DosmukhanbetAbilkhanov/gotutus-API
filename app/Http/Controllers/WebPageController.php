<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LegalPage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebPageController extends Controller
{
    private const SUPPORTED_LANGUAGES = ['en', 'ru', 'kz'];

    public function landing(Request $request): View
    {
        $lang = $this->resolveLanguage($request);

        return view('pages.landing', [
            'lang' => $lang,
            'currentLang' => $lang,
            'supportedLanguages' => self::SUPPORTED_LANGUAGES,
        ]);
    }

    public function privacyPolicy(Request $request): View
    {
        $lang = $this->resolveLanguage($request);

        $page = LegalPage::getActive(LegalPage::SLUG_PRIVACY_POLICY);

        $title = '';
        $content = '';
        $lastUpdated = null;
        $version = '';

        if ($page) {
            $dbLang = $lang === 'kz' ? 'kk' : $lang;
            $translation = $page->translations->firstWhere('language_code', $dbLang)
                ?? $page->translations->firstWhere('language_code', 'en');

            $title = $translation?->title ?? '';
            $content = $translation?->content ?? '';
            $lastUpdated = $page->published_at;
            $version = $page->version;
        }

        return view('pages.privacy-policy', [
            'lang' => $lang,
            'title' => $title,
            'content' => $content,
            'lastUpdated' => $lastUpdated,
            'version' => $version,
            'currentLang' => $lang,
            'supportedLanguages' => self::SUPPORTED_LANGUAGES,
        ]);
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
