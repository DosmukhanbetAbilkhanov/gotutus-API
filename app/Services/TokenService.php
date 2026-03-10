<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Str;

class TokenService
{
    /**
     * Access token expiration in minutes.
     */
    public const ACCESS_TOKEN_EXPIRATION = 15;

    /**
     * Refresh token expiration in days.
     */
    public const REFRESH_TOKEN_EXPIRATION_DAYS = 30;

    /**
     * Generate a new access token and refresh token pair for the given user.
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int, token_type: string}
     */
    public function createTokenPair(User $user, string $tokenName = 'mobile'): array
    {
        $expiresAt = now()->addMinutes(self::ACCESS_TOKEN_EXPIRATION);

        $accessToken = $user->createToken(
            $tokenName,
            ['*'],
            $expiresAt,
        )->plainTextToken;

        $refreshToken = $this->createRefreshToken($user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => self::ACCESS_TOKEN_EXPIRATION * 60,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Create a new refresh token for the user.
     */
    public function createRefreshToken(User $user): string
    {
        $plainToken = Str::random(64);
        $hashedToken = hash('sha256', $plainToken);

        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $hashedToken,
            'expires_at' => now()->addDays(self::REFRESH_TOKEN_EXPIRATION_DAYS),
        ]);

        return $plainToken;
    }

    /**
     * Validate a refresh token and return the associated user.
     * Returns null if the token is invalid or expired.
     */
    public function validateRefreshToken(string $plainToken): ?User
    {
        $hashedToken = hash('sha256', $plainToken);

        $refreshToken = RefreshToken::where('token', $hashedToken)->first();

        if (! $refreshToken) {
            return null;
        }

        if ($refreshToken->isExpired()) {
            $refreshToken->delete();

            return null;
        }

        return $refreshToken->user;
    }

    /**
     * Revoke a specific refresh token.
     */
    public function revokeRefreshToken(string $plainToken): void
    {
        $hashedToken = hash('sha256', $plainToken);

        RefreshToken::where('token', $hashedToken)->delete();
    }

    /**
     * Revoke all refresh tokens for a user.
     */
    public function revokeAllRefreshTokens(User $user): void
    {
        $user->refreshTokens()->delete();
    }

    /**
     * Revoke the current access token for a user.
     */
    public function revokeCurrentAccessToken(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }
    }

    /**
     * Prune expired refresh tokens.
     */
    public static function pruneExpiredRefreshTokens(): int
    {
        return RefreshToken::where('expires_at', '<', now())->delete();
    }
}
