<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Handle role-based dashboard routing after login
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Log role-based routing decision for debugging
        Log::info('HomeController: Role-based routing', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'is_admin' => $user->isAdmin(),
            'is_agent' => $user->isAgent(),
            'is_customer' => $user->isCustomer(),
            'can_access_system' => $user->canAccessSystem(),
        ]);
        
        // Route based on user role
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isAgent()) {
            return redirect()->route('agent.dashboard');
        } else {
            // For customers and other roles, use customer dashboard
            return redirect()->route('customer.dashboard');
        }
    }
    
    /**
     * Show the application home page for non-authenticated users
     */
    public function welcome()
    {
        return view('welcome');
    }
}
