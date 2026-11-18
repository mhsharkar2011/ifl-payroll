<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * ✅ Share global Inertia props
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            // ============================================================
            // AUTH — Always available on every page
            // ============================================================
            'auth' => [
                'user' => $user ? [
                    'id'             => $user->id,
                    'name'           => $user->name,
                    'email'          => $user->email,
                    'phone'          => $user->phone ?? null,
                    'avatar'         => $user->avatar,
                    'avatar_url'     => $user->avatar_url,
                    'theme'          => $user->theme ?? 'system',
                    'timezone'       => $user->timezone ?? config('app.timezone'),
                    'language'       => $user->language ?? 'en',
                    'email_verified' => !is_null($user->email_verified_at),
                    'roles'          => method_exists($user, 'getRoleNames')
                        ? $user->getRoleNames()->toArray()
                        : [],
                ] : null,
            ],

            // ============================================================
            // FLASH — Session messages
            // ============================================================
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'status'  => fn () => $request->session()->get('status'),
            ],
        ]);
    }
}
