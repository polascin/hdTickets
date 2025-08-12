<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TicketSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;

class UserContributionController extends Controller
{
    /**
     * Show the contribution form
     */
    public function create()
    {
        $platforms = TicketSource::getPlatforms();
        $statuses = TicketSource::getStatuses();

        return view('contributions.create', compact('platforms', 'statuses'));
    }

    /**
     * Store user contribution
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_name'          => 'required|string|max:255',
            'venue'               => 'required|string|max:255',
            'event_date'          => 'required|date|after:now',
            'platform'            => 'required|string|in:' . implode(',', array_keys(TicketSource::getPlatforms())),
            'price_min'           => 'nullable|numeric|min:0',
            'price_max'           => 'nullable|numeric|min:0|gte:price_min',
            'availability_status' => 'required|string|in:' . implode(',', array_keys(TicketSource::getStatuses())),
            'url'                 => 'nullable|url',
            'description'         => 'nullable|string|max:1000',
        ]);

        $contribution = TicketSource::create([
            'name'                => $request->event_name,
            'event_name'          => $request->event_name,
            'venue'               => $request->venue,
            'event_date'          => $request->event_date,
            'platform'            => $request->platform,
            'price_min'           => $request->price_min,
            'price_max'           => $request->price_max,
            'availability_status' => $request->availability_status,
            'url'                 => $request->url,
            'description'         => $request->description,
            'last_checked'        => now(),
            'is_active'           => FALSE, // Require admin approval
        ]);

        // Log the contribution for admin review
        Log::info('User contribution submitted', [
            'user_id'         => Auth::id(),
            'user_email'      => Auth::user()->email,
            'contribution_id' => $contribution->id,
            'event_name'      => $request->event_name,
        ]);

        return redirect()->route('contributions.create')
            ->with('success', 'Thank you for your contribution! It will be reviewed by our team.');
    }

    /**
     * Show pending contributions for admin review
     */
    public function pending()
    {
        $this->authorize('admin-access');

        $contributions = TicketSource::where('is_active', FALSE)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('contributions.pending', compact('contributions'));
    }

    /**
     * Approve a contribution
     */
    public function approve(TicketSource $contribution)
    {
        $this->authorize('admin-access');

        $contribution->update(['is_active' => TRUE]);

        return redirect()->route('contributions.pending')
            ->with('success', 'Contribution approved and activated.');
    }

    /**
     * Reject a contribution
     */
    public function reject(TicketSource $contribution)
    {
        $this->authorize('admin-access');

        $contribution->delete();

        return redirect()->route('contributions.pending')
            ->with('success', 'Contribution rejected and removed.');
    }
}
