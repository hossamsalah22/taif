<?php

if (! function_exists('fcm_subscribe')) {
    function fcm_subscribe(string $token, string $topic): bool
    {
        \Illuminate\Support\Facades\Log::info('[FCM Helper] Subscribing token to topic', [
            'fcm_token' => $token,
            'topic' => $topic,
        ]);

        return app(\App\Services\FcmTopicService::class)->subscribeToTopic($token, $topic);
    }
}

if (! function_exists('fcm_unsubscribe')) {
    function fcm_unsubscribe(string $token, string $topic): bool
    {
        \Illuminate\Support\Facades\Log::info('[FCM Helper] Unsubscribing token from topic', [
            'fcm_token' => $token,
            'topic' => $topic,
        ]);

        return app(\App\Services\FcmTopicService::class)->unsubscribeFromTopic($token, $topic);
    }
}

if (! function_exists('fcm_subscribe_all')) {
    /**
     * Subscribe a user's FCM token to all relevant topics (dispatched as background Job).
     * Topics based on user language:
     *   - general_ar / general_en   → all users
     *   - parents_ar / parents_en   → parent users
     */
    function fcm_subscribe_all($user, string $token): bool
    {
        if (! $user || ! $token) {
            return false;
        }

        \App\Jobs\SubscribeToTopicsJob::dispatch($user, $token);

        return true;
    }
}

if (! function_exists('fcm_subscribe_all_sync')) {
    /**
     * Internal helper – runs synchronously inside the Job.
     */
    function fcm_subscribe_all_sync($user, string $token): bool
    {
        $lang = $user->locale ?? 'ar';
        $otherLang = ($lang === 'ar') ? 'en' : 'ar';

        // Unsubscribe from old language topics first
        $oldTopics = ["general_{$otherLang}", "parents_{$otherLang}"];
        foreach ($oldTopics as $oldTopic) {
            fcm_unsubscribe($token, $oldTopic);
        }

        // Subscribe to current language topics
        $topics = ["general_{$lang}", "parents_{$lang}"];
        foreach ($topics as $topic) {
            fcm_subscribe($token, $topic);
        }

        return true;
    }
}

if (! function_exists('fcm_unsubscribe_all')) {
    /**
     * Unsubscribe a user's FCM token from all topics (dispatched as background Job).
     */
    function fcm_unsubscribe_all($user, string $token): bool
    {
        if (! $user || ! $token) {
            return false;
        }

        \App\Jobs\UnsubscribeFromTopicsJob::dispatch($user, $token);

        return true;
    }
}

if (! function_exists('fcm_unsubscribe_all_sync')) {
    /**
     * Internal helper – runs synchronously inside the Job.
     */
    function fcm_unsubscribe_all_sync($user, string $token): bool
    {
        $topics = [
            'general_ar', 'general_en',
            'parents_ar', 'parents_en',
        ];

        foreach ($topics as $topic) {
            fcm_unsubscribe($token, $topic);
        }

        return true;
    }
}
