<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmTopicService
{
    protected FcmService $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function subscribeToTopic(string $token, string $topic): bool
    {
        $accessToken = $this->fcmService->getAccessToken();
        if (! $accessToken) {
            Log::error('[FcmTopicService] Failed to get Access Token for subscription.');

            return false;
        }

        $response = Http::withToken($accessToken)
            ->withHeaders(['access_token_auth' => 'true'])
            ->post("https://iid.googleapis.com/iid/v1/{$token}/rel/topics/{$topic}");

        if ($response->failed()) {
            Log::error('[FcmTopicService] FCM Topic Subscription failed.', [
                'status' => $response->status(),
                'response' => $response->body(),
                'token' => $token,
                'topic' => $topic,
            ]);

            return false;
        }

        Log::info('[FcmTopicService] Successfully subscribed token to topic', ['token' => $token, 'topic' => $topic]);

        return true;
    }

    public function unsubscribeFromTopic(string $token, string $topic): bool
    {
        $accessToken = $this->fcmService->getAccessToken();
        if (! $accessToken) {
            Log::error('[FcmTopicService] Failed to get Access Token for unsubscription.');

            return false;
        }

        $response = Http::withToken($accessToken)
            ->withHeaders(['access_token_auth' => 'true'])
            ->delete("https://iid.googleapis.com/iid/v1/{$token}/rel/topics/{$topic}");

        if ($response->failed()) {
            Log::error('[FcmTopicService] FCM Topic Unsubscription failed.', [
                'status' => $response->status(),
                'response' => $response->body(),
                'token' => $token,
                'topic' => $topic,
            ]);

            return false;
        }

        Log::info('[FcmTopicService] Successfully unsubscribed token from topic', ['token' => $token, 'topic' => $topic]);

        return true;
    }
}
