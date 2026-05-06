<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function __construct(protected JwtService $jwt) {}

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->onlyInput('email');
        }

        if (! $user->email_verified_at) {
            return back()
                ->withErrors(['email' => 'Email belum diverifikasi. Cek inbox Anda atau minta link verifikasi baru.'])
                ->with('unverified_email', $credentials['email'])
                ->onlyInput('email');
        }

        $this->queueTokenCookie($user);

        return redirect($this->homeFor($user));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'role'     => 'mahasiswa',
        ]);

        EmailVerificationController::sendVerificationEmail($user);

        return redirect()->route('email.notice')
            ->with('registered_email', $user->email)
            ->with('status', 'Akun berhasil dibuat! Cek email Anda untuk melanjutkan verifikasi.');
    }

    public function logout()
    {
        Cookie::queue(Cookie::forget(JwtService::COOKIE_NAME));

        return redirect()->route('login');
    }

    private function queueTokenCookie(User $user): void
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

    private function homeFor(User $user): string
    {
        return $user->isAdmin() ? route('admin.dashboard') : route('mahasiswa.dashboard');
    }
}
