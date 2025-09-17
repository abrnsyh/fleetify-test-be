<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends BaseController
{
    public function login(Request $request)
    {
        $this->checkTooManyFailedAttempts();

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            RateLimiter::clear($this->throttleKey());

            return $this->sendResponse('Login Berhasil', ['user' => $user]);
        }

        RateLimiter::hit($this->throttleKey(), $seconds = 3600);

        return $this->sendError('user tidak di temukan', [], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        return $this->sendResponse('Logout Berhasil', []);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    private function throttleKey()
    {
        return Str::lower(request('email')).'|'.request()->ip();
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     */
    private function checkTooManyFailedAttempts()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 10)) {
            return;
        }

        throw new Exception('Terlalu banyak percobaan login. Silakan coba lagi nanti.', 429);
    }
}
