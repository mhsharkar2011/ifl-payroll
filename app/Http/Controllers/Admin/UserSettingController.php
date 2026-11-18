<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserSettingController extends Controller
{
    /* =========================================================
     |  PAGE
     * ========================================================= */

    public function index(Request $request)
    {
        $user = Auth::user();

        return Inertia::render('Admin/Settings/User', [
            'user'     => $this->userPayload($user),
            'sessions' => $this->getUserSessions($request),
            'timezones' => $this->getTimezones(),
            'languages' => $this->getLanguages(),
        ]);
    }

    /* =========================================================
     |  PROFILE
     * ========================================================= */

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $emailChanged = $validated['email'] !== $user->email;

        $user->fill($validated);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        $message = $emailChanged
            ? '✅ Profile updated. Please verify your new email.'
            : '✅ Profile updated successfully.';

        return back()->with('success', $message);
    }

    /* =========================================================
     |  AVATAR
     * ========================================================= */

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = Auth::user();

        // Delete old avatar
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return back()->with('success', '✅ Avatar updated.');
    }

    public function deleteAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return back()->with('success', '✅ Avatar removed.');
    }

    /* =========================================================
     |  PASSWORD
     * ========================================================= */

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', '✅ Password changed successfully.');
    }

    /* =========================================================
     |  PREFERENCES
     * ========================================================= */

    public function updatePreferences(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'timezone'             => ['nullable', 'string', 'max:64'],
            'language'             => ['nullable', 'string', 'max:8'],
            'theme'                => ['nullable', 'in:light,dark,system'],
            'notifications'        => ['nullable', 'array'],
            'notifications.email'  => ['boolean'],
            'notifications.push'   => ['boolean'],
            'notifications.weekly' => ['boolean'],
        ]);

        $user->update([
            'timezone'      => $validated['timezone'] ?? $user->timezone,
            'language'      => $validated['language'] ?? $user->language,
            'theme'         => $validated['theme'] ?? $user->theme,
            'notify_email'  => $validated['notifications']['email'] ?? false,
            'notify_push'   => $validated['notifications']['push'] ?? false,
            'notify_weekly' => $validated['notifications']['weekly'] ?? false,
        ]);

        return back()->with('success', '✅ Preferences saved.');
    }

    /* =========================================================
     |  EMAIL VERIFICATION
     ========================================================= */

    public function resendVerification(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return back()->with('success', 'Your email is already verified.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', '✅ Verification link sent to ' . $user->email);
    }

    /* =========================================================
     |  ACTIVE SESSIONS
     ========================================================= */

    protected function getUserSessions(Request $request): array
    {
        if (config('session.driver') !== 'database') {
            return [];
        }

        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get();

        $currentId = $request->session()->getId();

        return $sessions->map(function ($session) use ($currentId) {
            return [
                'id'            => $session->id,
                'ip_address'    => $session->ip_address,
                'user_agent'    => $session->user_agent,
                'last_activity' => date('c', $session->last_activity),
                'is_current'    => $session->id === $currentId,
                'browser'       => $this->parseUserAgent($session->user_agent),
            ];
        })->toArray();
    }

    public function destroySession(Request $request, string $sessionId)
    {
        if ($sessionId === $request->session()->getId()) {
            return back()->with('error', 'You cannot revoke the current session. Use Logout instead.');
        }

        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->delete();

        return back()->with('success', '✅ Session revoked.');
    }

    public function destroyOtherSessions(Request $request)
    {
        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return back()->with('success', '✅ All other sessions revoked.');
    }

    /* =========================================================
     |  DELETE ACCOUNT
     ========================================================= */

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.current_password' => 'The password you entered is incorrect.',
        ]);

        $user = $request->user();

        // Delete avatar
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Log out
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Delete user
        $user->delete();

        return redirect('/')->with('success', 'Your account has been deleted.');
    }

    /* =========================================================
     |  HELPERS
     * ========================================================= */

    protected function userPayload($user): array
    {
        // ✅ Fix: Explicitly build the storage URL so Vue receives a valid absolute URL
        $avatarUrl = null;
        if ($user->avatar) {
            $avatarUrl = asset('storage/' . $user->avatar);
        }

        return [
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'phone'           => $user->phone,
            'avatar'          => $user->avatar,
            'avatar_url'      => $avatarUrl, // ✅ Passing the explicit URL
            'timezone'        => $user->timezone ?? config('app.timezone'),
            'language'        => $user->language ?? 'en',
            'theme'           => $user->theme ?? 'system',
            'email_verified'  => !is_null($user->email_verified_at),
            'notifications'   => [
                'email'  => (bool) ($user->notify_email ?? true),
                'push'   => (bool) ($user->notify_push ?? false),
                'weekly' => (bool) ($user->notify_weekly ?? true),
            ],
            'roles'           => method_exists($user, 'getRoleNames')
                                    ? $user->getRoleNames()->toArray()
                                    : [],
            'created_at'      => $user->created_at?->toIso8601String(),
        ];
    }

    protected function parseUserAgent(?string $ua): array
    {
        $ua = $ua ?? '';

        $browser = 'Unknown';
        $platform = 'Unknown';

        if (preg_match('/Chrome\/(\d+)/', $ua, $m))         $browser = 'Chrome ' . $m[1];
        elseif (preg_match('/Firefox\/(\d+)/', $ua, $m))    $browser = 'Firefox ' . $m[1];
        elseif (preg_match('/Safari\/(\d+)/', $ua, $m))     $browser = 'Safari';
        elseif (preg_match('/Edg\/(\d+)/', $ua, $m))        $browser = 'Edge ' . $m[1];

        if (str_contains($ua, 'Windows'))       $platform = 'Windows';
        elseif (str_contains($ua, 'Mac OS'))    $platform = 'macOS';
        elseif (str_contains($ua, 'Linux'))     $platform = 'Linux';
        elseif (str_contains($ua, 'Android'))   $platform = 'Android';
        elseif (str_contains($ua, 'iPhone'))    $platform = 'iOS';

        return ['browser' => $browser, 'platform' => $platform];
    }

    protected function getTimezones(): array
    {
        return [
            ['value' => 'Asia/Dhaka',          'label' => '(GMT+06:00) Dhaka'],
            ['value' => 'Asia/Kolkata',        'label' => '(GMT+05:30) Kolkata'],
            ['value' => 'Asia/Karachi',        'label' => '(GMT+05:00) Karachi'],
            ['value' => 'Asia/Kathmandu',      'label' => '(GMT+05:45) Kathmandu'],
            ['value' => 'Asia/Dubai',          'label' => '(GMT+04:00) Dubai'],
            ['value' => 'Europe/London',       'label' => '(GMT+00:00) London'],
            ['value' => 'Europe/Paris',        'label' => '(GMT+01:00) Paris'],
            ['value' => 'America/New_York',    'label' => '(GMT-05:00) New York'],
            ['value' => 'America/Los_Angeles', 'label' => '(GMT-08:00) Los Angeles'],
            ['value' => 'Australia/Sydney',    'label' => '(GMT+10:00) Sydney'],
            ['value' => 'UTC',                 'label' => '(GMT+00:00) UTC'],
        ];
    }

    protected function getLanguages(): array
    {
        return [
            ['code' => 'en', 'label' => 'English'],
            ['code' => 'bn', 'label' => 'বাংলা (Bangla)'],
            ['code' => 'hi', 'label' => 'हिन्दी (Hindi)'],
            ['code' => 'ar', 'label' => 'العربية (Arabic)'],
        ];
    }
}
