<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        return view('auth.register', [
            'redirectTo' => $this->redirectTarget($request),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $redirectTo = $this->redirectTarget($request);

        if ($redirectTo) {
            return redirect()->to($redirectTo);
        }

        return redirect(route('dashboard', absolute: false));
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
