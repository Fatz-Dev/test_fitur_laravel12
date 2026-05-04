<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;

class JwtService
{
    public const COOKIE_NAME = 'kpm_token';
    public const TTL_MINUTES = 60 * 24 * 7; // 7 days

    public function issue(int $userId, string $role): string
    {
        $now = time();
        $payload = [
            'iss' => config('app.url'),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + self::TTL_MINUTES * 60,
            'jti' => (string) Str::uuid(),
            'sub' => $userId,
            'role' => $role,
        ];

        return JWT::encode($payload, $this->key(), 'HS256');
    }

    public function decode(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->key(), 'HS256'));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function key(): string
    {
        $appKey = config('app.key');
        if (str_starts_with((string) $appKey, 'base64:')) {
            return base64_decode(substr($appKey, 7));
        }

        return (string) $appKey;
    }
}
