<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates admin panel Socket.IO subscriptions on /broadcasting/auth.
 *
 * The socket server lives on another subdomain and cannot rely on the panel's session cookie,
 * so the panel embeds a short-lived encrypted token that the socket server forwards here.
 */
class AuthenticateAdminSocketToken
{
    public const HEADER = 'X-Admin-Socket-Token';

    public const TTL_HOURS = 24;

    public static function tokenFor(Admin $admin): string
    {
        return Crypt::encryptString(json_encode([
            'id' => $admin->id,
            'expires_at' => now()->addHours(self::TTL_HOURS)->timestamp,
        ]));
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header(self::HEADER);

        if ($token && ! Auth::guard('web')->check() && $admin = $this->resolveAdmin($token)) {
            Auth::guard('web')->setUser($admin);
        }

        return $next($request);
    }

    private function resolveAdmin(string $token): ?Admin
    {
        try {
            /** @var array{id?: int, expires_at?: int}|null $payload */
            $payload = json_decode(Crypt::decryptString($token), true);
        } catch (DecryptException) {
            return null;
        }

        if (! isset($payload['id'], $payload['expires_at']) || $payload['expires_at'] < now()->timestamp) {
            return null;
        }

        $admin = Admin::find($payload['id']);

        return $admin?->is_active ? $admin : null;
    }
}
