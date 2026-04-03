<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Cookie anonimo per anti-duplicato su sondaggi pubblici (solo utenti non autenticati).
 */
final class AnonymousVoteCookie
{
    public static function name(): string
    {
        return config('sondaggi.anonymous_vote_cookie', 'sm_vote_client');
    }

    public static function ensure(Request $request): ?Cookie
    {
        if (self::cookieValue($request) !== null) {
            return null;
        }
        $uuid = self::generateUuidV4();
        $secure = $request->isSecure() || config('app.env') === 'production';

        return Cookie::create(self::name(), $uuid)
            ->withExpires(strtotime('+1 year'))
            ->withPath('/')
            ->withSecure($secure)
            ->withHttpOnly(true)
            ->withSameSite('Lax');
    }

    public static function cookieValue(Request $request): ?string
    {
        $raw = $request->cookie(self::name(), '');
        if (! is_string($raw) || $raw === '') {
            return null;
        }
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $raw) !== 1) {
            return null;
        }

        return strtolower($raw);
    }

    private static function generateUuidV4(): string
    {
        $b = random_bytes(16);
        $b[6] = chr(ord($b[6]) & 0x0F | 0x40);
        $b[8] = chr(ord($b[8]) & 0x3F | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
    }
}
