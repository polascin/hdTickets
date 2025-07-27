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
use Illuminate\View\View;
use Illuminate\Http\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     * Only accessible by admin users.
     */
    public function create(): View|Response
    {
        // Check if user is authenticated and is an admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Access denied. User registration is restricted to administrators only.');
        }
        
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     * Only accessible by admin users.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Check if user is authenticated and is an admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Access denied. User registration is restricted to administrators only.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['sometimes', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['sometimes', 'string', 'in:admin,agent,customer,scraper'],
            'is_active' => ['sometimes', 'boolean'],
            'require_2fa' => ['sometimes', 'boolean'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'username' => $request->username ?? strtolower(str_replace(' ', '.', $request->name)),
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? User::ROLE_CUSTOMER,
            'is_active' => $request->is_active ?? true,
            'require_2fa' => $request->require_2fa ?? false,
            'registration_source' => 'admin',
            'created_by_type' => 'admin',
            'created_by_id' => Auth::id(),
            'password_changed_at' => now(),
        ]);

        event(new Registered($user));

        // Don't automatically log in the new user since this is admin registration
        // Redirect back to admin user management with success message
        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' has been successfully created with role '{$user->role}'.");
    }
}
