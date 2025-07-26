<?php

namespace App\Http\Controllers;

use App\Models\TicketSource;
use Illuminate\Http\Request;

class TicketSourceController extends Controller
{
    /**
     * Display a listing of ticket sources
     */
    public function index(Request $request)
    {
        $query = TicketSource::query();

        // Filter by platform
        if ($request->has('platform') && $request->platform) {
            $query->where('platform', $request->platform);
        }

        // Filter by availability
        if ($request->has('status') && $request->status) {
            $query->where('availability_status', $request->status);
        }

        // Filter by event name
        if ($request->has('search') && $request->search) {
            $query->where('event_name', 'like', '%' . $request->search . '%')
                  ->orWhere('venue', 'like', '%' . $request->search . '%');
        }

        // Filter by active status
        if (!$request->has('show_inactive')) {
            $query->where('is_active', true);
        }

        // Order by event date and last checked
        $ticketSources = $query->orderBy('event_date', 'asc')
                              ->orderBy('last_checked', 'desc')
                              ->paginate(20);

        $platforms = TicketSource::getPlatforms();
        $statuses = TicketSource::getStatuses();

        return view('ticket-sources.index', compact('ticketSources', 'platforms', 'statuses'));
    }

    /**
     * Show the form for creating a new ticket source
     */
    public function create()
    {
        $platforms = TicketSource::getPlatforms();
        $statuses = TicketSource::getStatuses();
        
        return view('ticket-sources.create', compact('platforms', 'statuses'));
    }

    /**
     * Store a newly created ticket source
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|string|in:' . implode(',', array_keys(TicketSource::getPlatforms())),
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date|after:now',
            'venue' => 'required|string|max:255',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
            'availability_status' => 'required|string|in:' . implode(',', array_keys(TicketSource::getStatuses())),
            'url' => 'nullable|url',
            'description' => 'nullable|string|max:1000',
        ]);

        TicketSource::create([
            'name' => $request->name,
            'platform' => $request->platform,
            'event_name' => $request->event_name,
            'event_date' => $request->event_date,
            'venue' => $request->venue,
            'price_min' => $request->price_min,
            'price_max' => $request->price_max,
            'availability_status' => $request->availability_status,
            'url' => $request->url,
            'description' => $request->description,
            'last_checked' => now(),
        ]);

        return redirect()->route('ticket-sources.index')
            ->with('success', 'Ticket source added successfully!');
    }

    /**
     * Display the specified ticket source
     */
    public function show(TicketSource $ticketSource)
    {
        return view('ticket-sources.show', compact('ticketSource'));
    }

    /**
     * Show the form for editing the specified ticket source
     */
    public function edit(TicketSource $ticketSource)
    {
        $platforms = TicketSource::getPlatforms();
        $statuses = TicketSource::getStatuses();
        
        return view('ticket-sources.edit', compact('ticketSource', 'platforms', 'statuses'));
    }

    /**
     * Update the specified ticket source
     */
    public function update(Request $request, TicketSource $ticketSource)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|string|in:' . implode(',', array_keys(TicketSource::getPlatforms())),
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'venue' => 'required|string|max:255',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
            'availability_status' => 'required|string|in:' . implode(',', array_keys(TicketSource::getStatuses())),
            'url' => 'nullable|url',
            'description' => 'nullable|string|max:1000',
        ]);

        $ticketSource->update([
            'name' => $request->name,
            'platform' => $request->platform,
            'event_name' => $request->event_name,
            'event_date' => $request->event_date,
            'venue' => $request->venue,
            'price_min' => $request->price_min,
            'price_max' => $request->price_max,
            'availability_status' => $request->availability_status,
            'url' => $request->url,
            'description' => $request->description,
            'last_checked' => now(),
        ]);

        return redirect()->route('ticket-sources.index')
            ->with('success', 'Ticket source updated successfully!');
    }

    /**
     * Remove the specified ticket source
     */
    public function destroy(TicketSource $ticketSource)
    {
        $ticketSource->delete();

        return redirect()->route('ticket-sources.index')
            ->with('success', 'Ticket source deleted successfully!');
    }

    /**
     * Toggle active status
     */
    public function toggle(TicketSource $ticketSource)
    {
        $ticketSource->update(['is_active' => !$ticketSource->is_active]);

        $status = $ticketSource->is_active ? 'activated' : 'deactivated';
        return redirect()->route('ticket-sources.index')
            ->with('success', "Ticket source {$status} successfully!");
    }

    /**
     * Get available tickets for API
     */
    public function apiIndex(Request $request)
    {
        $query = TicketSource::active()->available();

        if ($request->has('platform')) {
            $query->byPlatform($request->platform);
        }

        $tickets = $query->orderBy('event_date', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $tickets,
            'count' => $tickets->count()
        ]);
    }
}
