<?php

namespace App\Http\Controllers;

use App\Mail\VerifyEmailMail;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    public function __construct(protected JwtService $jwt) {}

    public function notice()
    {
        return view('auth.verify-email');
    }

    public function verify(Request $request, int $id)
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('email.notice')
                ->with('error', 'Link verifikasi tidak valid atau sudah kedaluwarsa. Silakan minta link baru.');
        }

        $user = User::findOrFail($id);

        if ($request->query('hash') !== sha1($user->email)) {
            abort(403);
        }

        if (! $user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        $this->issueTokenCookie($user);

        $home = $user->isAdmin()
            ? route('admin.dashboard')
            : route('mahasiswa.dashboard');

        return redirect($home)
            ->with('status', 'Email berhasil diverifikasi! Selamat datang di SIPEP.');
    }

    public function resend(Request $request)
    {
        $request->validate(['email' => ['required', 'email', 'exists:users,email']]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return redirect()->route('login')
                ->with('status', 'Email Anda sudah diverifikasi sebelumnya. Silakan masuk.');
        }

        $this->sendVerificationEmail($user);

        return back()->with('status', 'Link verifikasi baru telah dikirim ke ' . $user->email);
    }

    public static function sendVerificationEmail(User $user): void
    {
        $url = URL::temporarySignedRoute(
            'email.verify',
            now()->addHours(24),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        Mail::to($user->email)->send(new VerifyEmailMail($user, $url));
    }

    private function issueTokenCookie(User $user): void
    {
        $token = $this->jwt->issue($user->id, $user->role);
        Cookie::queue(
            JwtService::COOKIE_NAME,
            $token,
            JwtService::TTL_MINUTES,
            '/',
            null,
            request()->isSecure(),
            true,
            false,
            'lax'
        );
    }
}
