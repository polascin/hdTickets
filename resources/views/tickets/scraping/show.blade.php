@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ $ticket->event_title }}</h1>
        <a href="{{ route('tickets.scraping.index') }}" class="btn btn-secondary">← Back to Tickets</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Ticket Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Event:</strong> {{ $ticket->event_title }}<br>
                            <strong>Venue:</strong> {{ $ticket->venue ?? 'N/A' }}<br>
                            <strong>Date:</strong> {{ $ticket->event_date->format('M d, Y H:i') }}<br>
                            <strong>Platform:</strong> {{ ucfirst($ticket->platform) }}<br>
                        </div>
                        <div class="col-md-6">
                            <strong>Price:</strong> {{ $ticket->currency ?? 'USD' }} {{ $ticket->price }}<br>
                            <strong>Total Price:</strong> {{ $ticket->currency ?? 'USD' }} {{ $ticket->total_price }}<br>
                            <strong>Section:</strong> {{ $ticket->section ?? 'N/A' }}<br>
                            <strong>Row:</strong> {{ $ticket->row ?? 'N/A' }}<br>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Availability:</strong> 
                            <span class="badge bg-{{ $ticket->availability_status === 'available' ? 'success' : 'warning' }}">
                                {{ ucfirst($ticket->availability_status) }}
                            </span><br>
                            <strong>Quantity Available:</strong> {{ $ticket->quantity_available ?? 'N/A' }}<br>
                        </div>
                        <div class="col-md-6">
                            <strong>High Demand:</strong> 
                            <span class="badge bg-{{ $ticket->is_high_demand ? 'danger' : 'secondary' }}">
                                {{ $ticket->is_high_demand ? 'Yes' : 'No' }}
                            </span><br>
                            <strong>Demand Score:</strong> {{ $ticket->demand_score ?? 'N/A' }}<br>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <p><strong>Scraped At:</strong> {{ $ticket->scraped_at->format('M d, Y H:i:s') }}</p>
                    
                    @if($ticket->ticket_url)
                    <div class="mt-3">
                        <a href="{{ $ticket->ticket_url }}" target="_blank" class="btn btn-success">
                            🎫 View on {{ ucfirst($ticket->platform) }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Actions</h5>
                </div>
                <div class="card-body">
                    @if($ticket->availability_status === 'available')
                        <div class="d-grid gap-2">
                            @if($ticket->ticket_url)
                            <a href="{{ $ticket->ticket_url }}" target="_blank" class="btn btn-primary">
                                Buy Now on {{ ucfirst($ticket->platform) }}
                            </a>
                            @endif
                            
                            <button class="btn btn-outline-warning" onclick="addToWatchlist({{ $ticket->id }})">
                                Add to Watchlist
                            </button>
                            
                            <button class="btn btn-outline-info" onclick="setAlert({{ $ticket->id }})">
                                Set Price Alert
                            </button>
                        </div>
                        
                        @if($ticket->is_high_demand)
                        <div class="alert alert-warning mt-3">
                            <strong>🔥 High Demand!</strong><br>
                            This ticket is in high demand. Consider purchasing quickly.
                        </div>
                        @endif
                    @else
                        <div class="alert alert-secondary">
                            This ticket is currently not available.
                        </div>
                    @endif
                </div>
            </div>
            
            @if($ticket->metadata && count($ticket->metadata))
            <div class="card mt-3">
                <div class="card-header">
                    <h6>Additional Information</h6>
                </div>
                <div class="card-body">
                    @foreach($ticket->metadata as $key => $value)
                        <small><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</small><br>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function addToWatchlist(ticketId) {
    // Implement watchlist functionality
    alert('Added to watchlist (functionality to be implemented)');
}

function setAlert(ticketId) {
    // Implement price alert functionality
    alert('Price alert set (functionality to be implemented)');
}
</script>
@endsection
