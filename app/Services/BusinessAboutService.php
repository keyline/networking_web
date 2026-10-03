<?php

namespace App\Services;

class BusinessAboutService
{
    private const ALLOWED_TAGS = '<p><div><br><strong><b><em><i><ul><li>';

    /** Keep only the formatting offered by the compact business editor. */
    public static function sanitize(?string $html): string
    {
        $safeHtml = LegalPagesService::sanitize($html);

        return trim(strip_tags($safeHtml, self::ALLOWED_TAGS));
    }
}
