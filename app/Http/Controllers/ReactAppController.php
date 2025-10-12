<?php declare(strict_types=1);

namespace App\Http\Controllers;

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
