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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['sometimes', 'string', 'in:admin,agent,customer'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? User::ROLE_CUSTOMER, // Default to customer
        ]);

        event(new Registered($user));

        // Don't automatically log in the new user since this is admin registration
        // Redirect back to admin user management with success message
        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' has been successfully created with role '{$user->role}'.");
    }
}
