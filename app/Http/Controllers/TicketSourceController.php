<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TicketSource;
use Illuminate\Http\Request;

use function in_array;

class TicketSourceController extends Controller
{
    /**
     * Display a listing of ticket sources
     */
    /**
     * Index
     */
    public function index(): Illuminate\Contracts\View\View
    {
        $query = TicketSource::query()->with('category');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('event_name', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by platform
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        // Filter by availability status
        if ($request->filled('status')) {
            $query->where('availability_status', $request->status);
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        // Filter by currency
        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price_min', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_max', '<=', $request->max_price);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('event_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('event_date', '<=', $request->date_to);
        }

        // Filter by active status
        if (! $request->has('show_inactive')) {
            $query->where('is_active', TRUE);
        }

        // Filter by upcoming/past events
        if ($request->filled('time_filter')) {
            if ($request->time_filter === 'upcoming') {
                $query->upcoming();
            } elseif ($request->time_filter === 'past') {
                $query->past();
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'event_date');
        $sortOrder = $request->get('sort_order', 'asc');

        if (in_array($sortBy, ['event_date', 'last_checked', 'price_min', 'created_at', 'name'], TRUE)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('event_date', 'asc');
        }

        // Add secondary sort
        if ($sortBy !== 'last_checked') {
            $query->orderBy('last_checked', 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $ticketSources = $query->paginate($perPage)->appends($request->query());

        // Statistics for dashboard
        $stats = [
            'total'     => TicketSource::count(),
            'active'    => TicketSource::active()->count(),
            'available' => TicketSource::available()->count(),
            'upcoming'  => TicketSource::upcoming()->count(),
        ];

        $platforms = TicketSource::getPlatforms();
        $statuses = TicketSource::getStatuses();
        $countries = TicketSource::getCountries();
        $currencies = TicketSource::getCurrencies();

        return view('ticket-sources.index', compact(
            'ticketSources',
            'platforms',
            'statuses',
            'countries',
            'currencies',
            'stats',
        ));
    }

    /**
     * Show the form for creating a new ticket source
     */
    /**
     * Create
     */
    public function create(): Illuminate\Contracts\View\View
    {
        $platforms = TicketSource::getPlatforms();
        $statuses = TicketSource::getStatuses();

        return view('ticket-sources.create', compact('platforms', 'statuses'));
    }

    /**
     * Store a newly created ticket source
     */
    /**
     * Store
     */
    public function store(Request $request): Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'platform'            => 'required|string|in:' . implode(',', array_keys(TicketSource::getPlatforms())),
            'event_name'          => 'required|string|max:255',
            'event_date'          => 'required|date|after:now',
            'venue'               => 'required|string|max:255',
            'price_min'           => 'nullable|numeric|min:0',
            'price_max'           => 'nullable|numeric|min:0|gte:price_min',
            'currency'            => 'nullable|string|in:' . implode(',', array_keys(TicketSource::getCurrencies())),
            'country'             => 'nullable|string|in:' . implode(',', array_keys(TicketSource::getCountries())),
            'availability_status' => 'required|string|in:' . implode(',', array_keys(TicketSource::getStatuses())),
            'url'                 => 'nullable|url|max:500',
            'description'         => 'nullable|string|max:1000',
            'external_id'         => 'nullable|string|max:255',
        ]);

        TicketSource::create([
            'name'                => $request->name,
            'platform'            => $request->platform,
            'event_name'          => $request->event_name,
            'event_date'          => $request->event_date,
            'venue'               => $request->venue,
            'price_min'           => $request->price_min,
            'price_max'           => $request->price_max,
            'currency'            => $request->currency ?: 'GBP',
            'country'             => $request->country ?: 'GB',
            'language'            => 'en-GB',
            'availability_status' => $request->availability_status,
            'url'                 => $request->url,
            'description'         => $request->description,
            'external_id'         => $request->external_id,
            'last_checked'        => now(),
            'is_active'           => TRUE,
        ]);

        return redirect()->route('ticket-sources.index')
            ->with('success', 'Ticket source added successfully!');
    }

    /**
     * Display the specified ticket source
     */
    /**
     * Show
     */
    public function show(): Illuminate\Contracts\View\View
    {
        return view('ticket-sources.show', compact('ticketSource'));
    }

    /**
     * Show the form for editing the specified ticket source
     */
    /**
     * Edit
     */
    public function edit(): Illuminate\Contracts\View\View
    {
        $platforms = TicketSource::getPlatforms();
        $statuses = TicketSource::getStatuses();

        return view('ticket-sources.edit', compact('ticketSource', 'platforms', 'statuses'));
    }

    /**
     * Update the specified ticket source
     */
    /**
     * Update
     */
    public function update(Request $request): Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'platform'            => 'required|string|in:' . implode(',', array_keys(TicketSource::getPlatforms())),
            'event_name'          => 'required|string|max:255',
            'event_date'          => 'required|date',
            'venue'               => 'required|string|max:255',
            'price_min'           => 'nullable|numeric|min:0',
            'price_max'           => 'nullable|numeric|min:0',
            'availability_status' => 'required|string|in:' . implode(',', array_keys(TicketSource::getStatuses())),
            'url'                 => 'nullable|url',
            'description'         => 'nullable|string|max:1000',
        ]);

        $ticketSource->update([
            'name'                => $request->name,
            'platform'            => $request->platform,
            'event_name'          => $request->event_name,
            'event_date'          => $request->event_date,
            'venue'               => $request->venue,
            'price_min'           => $request->price_min,
            'price_max'           => $request->price_max,
            'availability_status' => $request->availability_status,
            'url'                 => $request->url,
            'description'         => $request->description,
            'last_checked'        => now(),
        ]);

        return redirect()->route('ticket-sources.index')
            ->with('success', 'Ticket source updated successfully!');
    }

    /**
     * Remove the specified ticket source
     *
     * @param mixed $ticketSource
     */
    /**
     * Destroy
     */
    public function destroy($ticketSource): Illuminate\Http\RedirectResponse
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
        $ticketSource->update(['is_active' => ! $ticketSource->is_active]);

        $status = $ticketSource->is_active ? 'activated' : 'deactivated';

        return redirect()->route('ticket-sources.index')
            ->with('success', "Ticket source {$status} successfully!");
    }

    /**
     * Bulk operations on ticket sources
     */
    /**
     * BulkAction
     */
    public function bulkAction(Request $request): Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids'    => 'required|array',
            'ids.*'  => 'exists:ticket_sources,id',
        ]);

        $count = 0;
        $action = $request->action;
        $ids = $request->ids;

        switch ($action) {
            case 'activate':
                $count = TicketSource::whereIn('id', $ids)->update(['is_active' => TRUE]);
                $message = "{$count} ticket sources activated successfully.";

                break;
            case 'deactivate':
                $count = TicketSource::whereIn('id', $ids)->update(['is_active' => FALSE]);
                $message = "{$count} ticket sources deactivated successfully.";

                break;
            case 'delete':
                $count = TicketSource::whereIn('id', $ids)->delete();
                $message = "{$count} ticket sources deleted successfully.";

                break;
        }

        return redirect()->route('ticket-sources.index')
            ->with('success', $message);
    }

    /**
     * Refresh/check status of ticket source
     */
    public function refresh(TicketSource $ticketSource)
    {
        // Update last_checked timestamp
        $ticketSource->update(['last_checked' => now()]);

        // Here you could add logic to actually check the ticket source
        // For now, we just update the timestamp

        return redirect()->back()
            ->with('success', 'Ticket source refreshed successfully!');
    }

    /**
     * Export ticket sources to CSV
     */
    /**
     * Export
     */
    public function export(Request $request): Illuminate\Http\RedirectResponse
    {
        $query = TicketSource::query();

        // Apply same filters as index
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }
        if ($request->filled('status')) {
            $query->where('availability_status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('event_name', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $ticketSources = $query->get();

        $filename = 'ticket_sources_' . date('Y_m_d_H_i_s') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ];

        $callback = function () use ($ticketSources): void {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID', 'Name', 'Platform', 'Event Name', 'Event Date', 'Venue',
                'Price Min', 'Price Max', 'Currency', 'Country', 'Status', 'URL',
                'Active', 'Last Checked', 'Created At',
            ]);

            // CSV data
            foreach ($ticketSources as $source) {
                fputcsv($file, [
                    $source->id,
                    $source->name,
                    $source->platform_name,
                    $source->event_name,
                    $source->event_date ? $source->event_date->format('Y-m-d H:i:s') : '',
                    $source->venue,
                    $source->price_min,
                    $source->price_max,
                    $source->currency,
                    $source->country,
                    $source->status_name,
                    $source->url,
                    $source->is_active ? 'Yes' : 'No',
                    $source->last_checked ? $source->last_checked->format('Y-m-d H:i:s') : 'Never',
                    $source->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get statistics for dashboard widget
     */
    public function stats()
    {
        $stats = [
            'total'        => TicketSource::count(),
            'active'       => TicketSource::active()->count(),
            'available'    => TicketSource::available()->count(),
            'upcoming'     => TicketSource::upcoming()->count(),
            'sold_out'     => TicketSource::where('availability_status', TicketSource::STATUS_SOLD_OUT)->count(),
            'platforms'    => TicketSource::distinct('platform')->count(),
            'countries'    => TicketSource::distinct('country')->count(),
            'last_checked' => TicketSource::whereNotNull('last_checked')
                ->orderBy('last_checked', 'desc')
                ->first()?->last_checked,
        ];

        return response()->json($stats);
    }

    /**
     * Get available tickets for API
     */
    /**
     * ApiIndex
     */
    public function apiIndex(Request $request): Illuminate\Http\RedirectResponse
    {
        $query = TicketSource::active()->available();

        if ($request->has('platform')) {
            $query->byPlatform($request->platform);
        }

        if ($request->has('country')) {
            $query->byCountry($request->country);
        }

        if ($request->has('currency')) {
            $query->byCurrency($request->currency);
        }

        if ($request->has('upcoming') && $request->upcoming) {
            $query->upcoming();
        }

        $tickets = $query->orderBy('event_date', 'asc')
            ->limit($request->get('limit', 50))
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $tickets,
            'count'  => $tickets->count(),
            'meta'   => [
                'total_available' => TicketSource::available()->count(),
                'platforms'       => $tickets->pluck('platform')->unique()->values(),
                'countries'       => $tickets->pluck('country')->unique()->values(),
            ],
        ]);
    }
}
