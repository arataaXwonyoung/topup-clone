@extends('layouts.app')

@section('title', 'Support Tickets')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="glass rounded-xl p-6 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-yellow-400 mb-2">Support Center</h1>
                <p class="text-gray-400">Manage your support tickets</p>
            </div>
            <a href="{{ route('user.support.create') }}" 
               class="px-6 py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                <i data-lucide="plus" class="inline w-5 h-5 mr-2"></i>
                New Ticket
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="glass rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Tickets</p>
                    <p class="text-2xl font-bold">{{ $stats['total_tickets'] ?? 0 }}</p>
                </div>
                <i data-lucide="ticket" class="w-8 h-8 text-gray-600"></i>
            </div>
        </div>

        <div class="glass rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Open Tickets</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ $stats['open_tickets'] ?? 0 }}</p>
                </div>
                <i data-lucide="mail-open" class="w-8 h-8 text-yellow-600"></i>
            </div>
        </div>

        <div class="glass rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Resolved</p>
                    <p class="text-2xl font-bold text-green-400">{{ $stats['resolved_tickets'] ?? 0 }}</p>
                </div>
                <i data-lucide="check-circle" class="w-8 h-8 text-green-600"></i>
            </div>
        </div>

        <div class="glass rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Avg Response</p>
                    <p class="text-xl font-bold">{{ $stats['average_response_time'] ?? 'N/A' }}</p>
                </div>
                <i data-lucide="clock" class="w-8 h-8 text-gray-600"></i>
            </div>
        </div>
    </div>

    <!-- Tickets List -->
    <div class="glass rounded-xl overflow-hidden">
        @if(isset($tickets) && $tickets->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-800/50 border-b border-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Ticket #</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Subject</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Priority</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Last Update</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($tickets as $ticket)
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm">{{ $ticket->ticket_number ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium">{{ $ticket->subject }}</p>
                                    @if($ticket->unread_count > 0)
                                        <span class="inline-block px-2 py-0.5 text-xs bg-red-500 text-white rounded-full">
                                            {{ $ticket->unread_count }} new
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm">{{ $ticket->category_label ?? $ticket->category }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full {{ $ticket->status_color ?? 'bg-gray-500' }} text-white">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full {{ $ticket->priority_color ?? 'bg-gray-500' }} text-white">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm">{{ $ticket->updated_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('user.support.show', $ticket->ticket_number ?? $ticket->id) }}" 
                                   class="text-yellow-400 hover:text-yellow-300">
                                    View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-gray-700">
                {{ $tickets->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <i data-lucide="inbox" class="w-16 h-16 text-gray-600 mx-auto mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-300 mb-2">No Support Tickets</h3>
                <p class="text-gray-400 mb-6">You haven't created any support tickets yet</p>
                <a href="{{ route('user.support.create') }}" 
                   class="inline-block px-6 py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                    Create Your First Ticket
                </a>
            </div>
        @endif
    </div>

    <!-- FAQ Section -->
    <div class="mt-8 glass rounded-xl p-6">
        <h2 class="text-xl font-semibold text-yellow-400 mb-4">Frequently Asked Questions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="#" class="p-4 bg-gray-800/50 rounded-lg hover:bg-gray-800/70 transition">
                <h3 class="font-medium mb-1">How do I track my order?</h3>
                <p class="text-sm text-gray-400">You can track your order status in the order history section.</p>
            </a>
            <a href="#" class="p-4 bg-gray-800/50 rounded-lg hover:bg-gray-800/70 transition">
                <h3 class="font-medium mb-1">Payment failed, what should I do?</h3>
                <p class="text-sm text-gray-400">Check your payment method and try again or contact support.</p>
            </a>
            <a href="#" class="p-4 bg-gray-800/50 rounded-lg hover:bg-gray-800/70 transition">
                <h3 class="font-medium mb-1">How long does delivery take?</h3>
                <p class="text-sm text-gray-400">Most orders are delivered instantly after payment confirmation.</p>
            </a>
            <a href="#" class="p-4 bg-gray-800/50 rounded-lg hover:bg-gray-800/70 transition">
                <h3 class="font-medium mb-1">Can I get a refund?</h3>
                <p class="text-sm text-gray-400">Refunds are available under certain conditions. Contact support for details.</p>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush