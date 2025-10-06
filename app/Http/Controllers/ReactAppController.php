<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReactAppController extends Controller
{
    /**
     * Display the React application
     */
    public function index()
    {
        return view('react-app');
    }
    
    /**
     * Handle React routing (catch-all for SPA)
     */
    public function catchAll()
    {
        return view('react-app');
    }
}