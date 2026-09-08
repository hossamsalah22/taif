<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected string $projectId;

    protected string $credentialsPath;

    public function __construct()
    {
        $this->projectId = config('fcm.project_id');
        $this->credentialsPath = config('fcm.credentials_path');
    }

    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        $message = [
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'apns' => [
                'payload' => ['aps' => ['sound' => 'default']],
            ],
            'android' => ['priority' => 'high'],
        ];

        if (! empty($data)) {
            $message['data'] = $data;
        }

        return $this->send($message);
    }

    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        $message = [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'apns' => [
                'payload' => ['aps' => ['sound' => 'default']],
            ],
            'android' => ['priority' => 'high'],
        ];

        if (! empty($data)) {
            $message['data'] = $data;
        }

        return $this->send($message);
    }

    protected function send(array $message): bool
    {
        Log::info('[FcmService] Attempting to send message to FCM', ['message' => $message]);

        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            Log::error('[FcmService] Failed to get Access Token.');

            return false;
        }

        $response = Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
                'message' => $message,
            ]);

        if ($response->failed()) {
            Log::error('[FcmService] FCM HTTP request failed.', [
                'status' => $response->status(),
                'response' => $response->body(),
                'message' => $message,
            ]);

            return false;
        }

        Log::info('[FcmService] Successfully sent message to FCM', ['response' => $response->json()]);

        return true;
    }

    public function getAccessToken(): ?string
    {
        try {
            if (! file_exists($this->credentialsPath)) {
                Log::error("[FcmService] Credentials file not found at: {$this->credentialsPath}");

                return null;
            }

            $credentials = json_decode(file_get_contents($this->credentialsPath), true);
            if (! $credentials || ! isset($credentials['client_email']) || ! isset($credentials['private_key'])) {
                Log::error("[FcmService] Invalid credentials file at: {$this->credentialsPath}");

                return null;
            }

            $jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $now = time();
            $jwtClaim = base64_encode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ]));

            $unsignedJwt = $jwtHeader.'.'.$jwtClaim;
            openssl_sign($unsignedJwt, $signature, $credentials['private_key'], 'SHA256');
            $jwt = $unsignedJwt.'.'.base64_encode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('[FcmService] Failed to fetch access token via JWT', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('[FcmService] Error fetching access token.', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
