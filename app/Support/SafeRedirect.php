<?php

declare(strict_types=1);

namespace App\Support;

final class SafeRedirect
{
    /**
     * Consente solo path interni sicuri (es. compilazione sondaggio dopo login).
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
