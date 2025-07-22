@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Scraped Tickets</h1>

    <div class="card mb-4">
        <div class="card-header">
            <form method="GET" action="{{ route('tickets.scraping.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="platform" class="form-label">Platform</label>
                        <input type="text" class="form-control" name="platform" value="{{ request('platform') }}" placeholder="Platform">
                    </div>
                    <div class="col-md-4">
                        <label for="keywords" class="form-label">Keywords</label>
                        <input type="text" class="form-control" name="keywords" value="{{ request('keywords') }}" placeholder="Keywords">
                    </div>
                    <div class="col-md-4">
                        <label for="max_price" class="form-label">Max Price</label>
                        <input type="number" class="form-control" name="max_price" value="{{ request('max_price') }}" placeholder="Max Price">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="{{ route('tickets.scraping.index') }}" class="btn btn-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($tickets->count())
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Price</th>
                        <th>Availability</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->id }}</td>
                        <td>{{ $ticket->title }}</td>
                        <td>{{ $ticket->event_date ? $ticket->event_date->format('M d, Y') : 'TBD' }}</td>
                        <td>
                            @if($ticket->min_price && $ticket->max_price)
                                {{ $ticket->currency }} {{ number_format($ticket->min_price, 2) }} - {{ number_format($ticket->max_price, 2) }}
                            @elseif($ticket->max_price)
                                {{ $ticket->currency }} {{ number_format($ticket->max_price, 2) }}
                            @elseif($ticket->min_price)
                                {{ $ticket->currency }} {{ number_format($ticket->min_price, 2) }}
                            @else
                                Price on request
                            @endif
                        </td>
                        <td>
                            @if($ticket->is_available)
                                <span class="badge bg-success">Available</span>
                            @else
                                <span class="badge bg-secondary">Sold Out</span>
                            @endif
                            @if($ticket->is_high_demand)
                                <span class="badge bg-warning">High Demand</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('tickets.scraping.show', $ticket) }}" class="btn btn-outline-primary btn-sm">View</a>
                            @if($ticket->ticket_url)
                                <a href="{{ $ticket->ticket_url }}" target="_blank" class="btn btn-success btn-sm">Buy</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $tickets->appends(request()->query())->links() }}
        </div>
    @else
        <div class="alert alert-warning">No scraped tickets found.</div>
    @endif

</div>
@endsection

