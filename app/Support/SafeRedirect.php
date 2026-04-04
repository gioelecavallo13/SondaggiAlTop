<?php

declare(strict_types=1);

namespace App\Support;

final class SafeRedirect
{
    /**
     * Fallback quando in sessione non c’è `url.intended` (es. link con `?redirect=` o campo hidden nei form auth).
     * Dopo login/register si usa `redirect()->intended(SafeRedirect::afterLogin(...))`: se il middleware `auth`
     * ha salvato l’URL del sondaggio, quello ha priorità; altrimenti si valida il candidato qui (whitelist path).
     */
    public static function afterLogin(?string $candidate): string
    {
        if ($candidate === null || $candidate === '') {
            return '/dashboard';
        }
        $candidate = trim($candidate);
        if (! str_starts_with($candidate, '/') || str_contains($candidate, "\0")) {
            return '/dashboard';
        }
        if (preg_match('#^/sondaggi/\d+/?$#', $candidate) === 1) {
            return preg_replace('#/+$#', '', $candidate) ?: '/dashboard';
        }

        return '/dashboard';
    }
}
