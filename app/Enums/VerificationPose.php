<?php

declare(strict_types=1);

namespace App\Enums;

enum VerificationPose: string
{
    case PeaceSign = 'peace_sign';
    case ThumbsUp = 'thumbs_up';
    case HandOnCheek = 'hand_on_cheek';
    case PointUp = 'point_up';
    case OpenPalm = 'open_palm';

    /**
     * Human-readable English instruction (used in admin panel).
     * Clients localize via the pose key.
     */
    public function label(): string
    {
        return match ($this) {
            self::PeaceSign => 'Make a peace sign (✌️) next to your face',
            self::ThumbsUp => 'Give a thumbs up (👍) next to your face',
            self::HandOnCheek => 'Place your open hand on your cheek',
            self::PointUp => 'Point up with one finger (☝️) next to your face',
            self::OpenPalm => 'Show your open palm (🖐️) facing the camera',
        };
    }

    public static function random(): self
    {
        $cases = self::cases();

        return $cases[array_rand($cases)];
    }
}
