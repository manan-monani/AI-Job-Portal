<?php

namespace App\Http\Controllers\Guest;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function __construct(protected \App\Services\UserService $userService) {}

    public function showLogin()
    {
        return Inertia::render('Guest/Pages/Login');
    }

    public function storeLogin(\App\Http\Requests\Auth\LoginRequest $request)
    {
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->isAdmin() || $user->isSuperAdmin()) {
                Auth::logout();

                return redirect()->back()->with('error', __('Admins must login via the Admin Portal.'));
            }

            return redirect()->intended(route('customer.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function showRegister()
    {
        return Inertia::render('Guest/Pages/Register');
    }

    public function storeRegister(\App\Http\Requests\Auth\RegisterRequest $request)
    {
        $validated = $request->validated();

        $validated['type'] = UserType::CUSTOMER;

        $user = $this->userService->create($validated);

        Auth::login($user);

        return redirect(route('customer.dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
