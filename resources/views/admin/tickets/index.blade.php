<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ticket Management') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('tickets.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create New Ticket
                </a>
                <a href="{{ route('tickets.scraping.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    View Scraped Tickets
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filters and Search -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.tickets.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All Statuses</option>
                                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                            <select name="priority" id="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All Priorities</option>
                                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assigned To</label>
                            <select name="assigned_to" id="assigned_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All Agents</option>
                                @if(isset($agents))
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ request('assigned_to') == $agent->id ? 'selected' : '' }}>
                                            {{ ($agent->name ?? 'Unknown') . ($agent->surname ? ' ' . $agent->surname : '') }}{{ $agent->username ? ' (' . $agent->username . ')' : '' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">{{ $stats['total'] ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Tickets</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['total'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">{{ $stats['open'] ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Open Tickets</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['open'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">{{ $stats['in_progress'] ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['in_progress'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">{{ $stats['resolved'] ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Resolved</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['resolved'] ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($tickets ?? [] as $ticket)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">#{{ $ticket->id }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ Str::limit($ticket->subject, 50) }}</div>
                                        <div class="text-sm text-gray-500">{{ $ticket->category->name ?? 'Uncategorized' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($ticket->user)
                                            <div class="text-sm font-medium text-gray-900">{{ ($ticket->user->name ?? 'Unknown') . ($ticket->user->surname ? ' ' . $ticket->user->surname : '') }}</div>
                                            <div class="text-sm text-gray-500">{{ $ticket->user->email ?? 'N/A' }}</div>
                                            @if($ticket->user->username)
                                                <div class="text-xs text-gray-400">{{ $ticket->user->username }}</div>
                                            @endif
                                        @else
                                            <div class="text-sm text-gray-900">N/A</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($ticket->status === 'open') bg-yellow-100 text-yellow-800 
                                            @elseif($ticket->status === 'in_progress') bg-blue-100 text-blue-800 
                                            @elseif($ticket->status === 'resolved') bg-green-100 text-green-800 
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($ticket->priority === 'low') bg-gray-100 text-gray-800 
                                            @elseif($ticket->priority === 'medium') bg-yellow-100 text-yellow-800 
                                            @elseif($ticket->priority === 'high') bg-orange-100 text-orange-800 
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($ticket->assignedTo)
                                            {{ ($ticket->assignedTo->name ?? 'Unknown') . ($ticket->assignedTo->surname ? ' ' . $ticket->assignedTo->surname : '') }}
                                            @if($ticket->assignedTo->username)
                                                <div class="text-xs text-gray-400">{{ $ticket->assignedTo->username }}</div>
                                            @endif
                                        @else
                                            Unassigned
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $ticket->created_at->format('M d, Y') }}
                                        <div class="text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                            <button onclick="assignTicket({{ $ticket->id }})" class="text-green-600 hover:text-green-900">Assign</button>
                                            <button onclick="updateStatus({{ $ticket->id }})" class="text-yellow-600 hover:text-yellow-900">Update</button>
                                            <button onclick="updatePriority({{ $ticket->id }})" class="text-orange-600 hover:text-orange-900">Priority</button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        No tickets found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if(isset($tickets) && method_exists($tickets, 'links'))
                    <div class="mt-4">
                        {{ $tickets->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Modal -->
    <div id="assignModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Ticket</h3>
                <form id="assignForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="agent_id" class="block text-sm font-medium text-gray-700">Select Agent</label>
                        <select name="agent_id" id="agent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Unassigned</option>
                            @if(isset($agents))
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">
                                        {{ ($agent->name ?? 'Unknown') . ($agent->surname ? ' ' . $agent->surname : '') }}{{ $agent->username ? ' (' . $agent->username . ')' : '' }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('assignModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Assign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Update Status</h3>
                <form id="statusForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">New Status</label>
                        <select name="status" id="new_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('statusModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Priority Update Modal -->
    <div id="priorityModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Update Priority</h3>
                <form id="priorityForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label for="priority" class="block text-sm font-medium text-gray-700">New Priority</label>
                        <select name="priority" id="new_priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('priorityModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function assignTicket(ticketId) {
            document.getElementById('assignForm').action = `/admin/tickets/${ticketId}/assign`;
            document.getElementById('assignModal').classList.remove('hidden');
        }

        function updateStatus(ticketId) {
            document.getElementById('statusForm').action = `/admin/tickets/${ticketId}/status`;
            document.getElementById('statusModal').classList.remove('hidden');
        }

        function updatePriority(ticketId) {
            document.getElementById('priorityForm').action = `/admin/tickets/${ticketId}/priority`;
            document.getElementById('priorityModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('fixed')) {
                e.target.classList.add('hidden');
            }
        });
    </script>
    @endpush
</x-app-layout>
