<?php

declare(strict_types=1);

namespace App\Services;

class TransliterationService
{
    /**
     * Cyrillic-to-Latin character mapping.
     * Based on GOST 7.79-2000 System B with Kazakh-specific characters.
     */
    private const CHAR_MAP = [
        // Russian uppercase
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
        'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
        'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch',
        'Ш' => 'Sh', 'Щ' => 'Shch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '',
        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',

        // Russian lowercase
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
        'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',

        // Kazakh-specific uppercase
        'Ә' => 'A', 'Ғ' => 'Gh', 'Қ' => 'Q', 'Ң' => 'Ng',
        'Ө' => 'O', 'Ұ' => 'U', 'Ү' => 'U', 'Һ' => 'H', 'І' => 'I',

        // Kazakh-specific lowercase
        'ә' => 'a', 'ғ' => 'gh', 'қ' => 'q', 'ң' => 'ng',
        'ө' => 'o', 'ұ' => 'u', 'ү' => 'u', 'һ' => 'h', 'і' => 'i',
    ];

    /**
     * Transliterate a Cyrillic string to Latin characters.
     *
     * Preserves Latin characters, digits, and punctuation as-is.
     * If the string contains no Cyrillic characters, returns it unchanged.
     */
    public function transliterate(string $text): string
    {
        if (!$this->hasCyrillic($text)) {
            return $text;
        }

        $result = '';
        $chars = mb_str_split($text);

        foreach ($chars as $char) {
            $result .= self::CHAR_MAP[$char] ?? $char;
        }

        return $result;
    }

    /**
     * Check if a string contains any Cyrillic characters.
     */
    private function hasCyrillic(string $text): bool
    {
        return (bool) preg_match('/[\p{Cyrillic}]/u', $text);
    }
}
