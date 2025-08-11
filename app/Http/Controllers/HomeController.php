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
        
        
        // Simple role-based routing
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'agent') {
            return redirect()->route('agent.dashboard');
        } elseif ($user->role === 'scraper') {
            return redirect()->route('scraper.dashboard');
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
