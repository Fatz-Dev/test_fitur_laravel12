<?php

namespace App\Auth;

use App\Services\JwtService;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class JwtGuard implements Guard
{
    use GuardHelpers;

    protected ?Authenticatable $resolvedUser = null;
    protected bool $resolved = false;

    public function __construct(
        UserProvider $provider,
        protected Request $request,
        protected JwtService $jwt,
    ) {
        $this->provider = $provider;
    }

    public function user(): ?Authenticatable
    {
        if ($this->resolved) {
            return $this->resolvedUser;
        }
        $this->resolved = true;

        $token = $this->request->cookie(JwtService::COOKIE_NAME)
            ?: $this->bearerToken();

        if (! $token) {
            return $this->resolvedUser = null;
        }

        $payload = $this->jwt->decode($token);
        if (! $payload || ! isset($payload->sub)) {
            return $this->resolvedUser = null;
        }

        $user = $this->provider->retrieveById($payload->sub);

        return $this->resolvedUser = $user;
    }

    public function validate(array $credentials = []): bool
    {
        if (empty($credentials['email']) || empty($credentials['password'])) {
            return false;
        }

        $user = $this->provider->retrieveByCredentials($credentials);
        if (! $user) {
            return false;
        }

        return $this->provider->validateCredentials($user, $credentials);
    }

    public function attempt(array $credentials = []): bool
    {
        $user = $this->provider->retrieveByCredentials($credentials);
        if (! $user || ! $this->provider->validateCredentials($user, $credentials)) {
            return false;
        }

        $this->setUser($user);

        return true;
    }

    public function setUser(Authenticatable $user): void
    {
        $this->resolvedUser = $user;
        $this->resolved = true;
    }

    protected function bearerToken(): ?string
    {
        $header = $this->request->header('Authorization', '');
        if (preg_match('/Bearer\s+(.+)$/i', (string) $header, $m)) {
            return $m[1];
        }
        return null;
    }
}
