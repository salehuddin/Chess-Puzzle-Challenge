<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        return view('auth.login', [
            'redirectTo' => $this->redirectTarget($request),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $redirectTo = $this->redirectTarget($request);

        if ($redirectTo) {
            return redirect()->to($redirectTo);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function redirectTarget(Request $request): ?string
    {
        $target = trim((string) $request->input('redirect_to', ''));

        if ($target === '' || str_starts_with($target, '//') || str_starts_with($target, 'http://') || str_starts_with($target, 'https://')) {
            return null;
        }

        return str_starts_with($target, '/') ? $target : '/'.ltrim($target, '/');
    }
}
