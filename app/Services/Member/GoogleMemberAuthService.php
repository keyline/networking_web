<?php

namespace App\Services\Member;

use Google\Client;
use Illuminate\Validation\ValidationException;

class GoogleMemberAuthService
{
    public function authorizationUrl(string $state): string
    {
        $client = $this->client();
        $client->setState($state);
        return $client->createAuthUrl();
    }

    public function profileFromCode(string $code): array
    {
        $client = $this->client();
        $token = $client->fetchAccessTokenWithAuthCode($code);
        if (isset($token['error'])) {
            throw ValidationException::withMessages(['google' => ['Google could not authenticate this login. Please try again.']]);
        }
        $payload = $client->verifyIdToken($token['id_token'] ?? null);
        if (!$payload || empty($payload['email']) || empty($payload['email_verified'])) {
            throw ValidationException::withMessages(['google' => ['Google did not return a verified email address.']]);
        }
        return $payload;
    }

    private function client(): Client
    {
        $id = config('services.google.client_id');
        $secret = config('services.google.client_secret');
        if (!$id || !$secret) {
            throw ValidationException::withMessages(['google' => ['Google login has not been configured yet.']]);
        }
        $client = new Client();
        $client->setClientId($id);
        $client->setClientSecret($secret);
        $client->setRedirectUri(route('member.google.callback'));
        $client->setScopes(['openid', 'email', 'profile']);
        $client->setPrompt('select_account');
        return $client;
    }
}
