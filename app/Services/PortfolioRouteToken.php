<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class PortfolioRouteToken
{
    public function encode(int $companyId): string
    {
        return rtrim(strtr(Crypt::encryptString((string) $companyId), '+/', '-_'), '=');
    }

    public function decode(string $token): int
    {
        $encoded = strtr($token, '-_', '+/');
        $encoded .= str_repeat('=', (4 - strlen($encoded) % 4) % 4);

        try {
            $companyId = Crypt::decryptString($encoded);
        } catch (DecryptException) {
            abort(404);
        }

        abort_unless(ctype_digit($companyId) && (int) $companyId > 0, 404);
        return (int) $companyId;
    }
}
