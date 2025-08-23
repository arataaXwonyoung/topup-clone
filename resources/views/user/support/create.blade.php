@extends('layouts.app')

@section('title', 'Create Support Ticket')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="glass rounded-xl p-6 mb-8">
        <h1 class="text-3xl font-bold text-yellow-400 mb-2">Create Support Ticket</h1>
        <p class="text-gray-400">Tell us how we can help you</p>
    </div>

    <div class="glass rounded-xl p-6">
        <form method="POST" action="{{ route('user.support.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Category</label>
                    <select name="category" 
                            class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                            required>
                        <option value="">Select Category</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Priority</label>
                    <select name="priority" 
                            class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                        <option value="low">Low</option>
                        <option value="normal" selected>Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Subject</label>
                <input type="text" 
                       name="subject"
                       class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                       placeholder="Brief description of your issue"
                       required>
                @error('subject')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            @if(isset($recentOrders) && $recentOrders->count() > 0)
            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Related Order (Optional)</label>
                <select name="order_id" 
                        class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                    <option value="">No related order</option>
                    @foreach($recentOrders as $order)
                        <option value="{{ $order->id }}">
                            {{ $order->invoice_no }} - {{ $order->game->name ?? 'N/A' }} - {{ $order->created_at->format('d M Y') }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Message</label>
                <textarea name="message" 
                          rows="6"
                          class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none"
                          placeholder="Describe your issue in detail..."
                          required></textarea>
                @error('message')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Attachments (Optional)</label>
                <input type="file" 
                       name="attachments[]"
                       multiple
                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                       class="w-full px-4 py-2 bg-gray-800 rounded-lg border border-gray-700 focus:border-yellow-400 focus:outline-none">
                <p class="mt-1 text-xs text-gray-400">Max 5 files. Allowed: JPG, PNG, PDF, DOC</p>
            </div>

            <div class="flex space-x-4">
                <button type="submit" 
                        class="px-6 py-3 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500 transition">
                    <i data-lucide="send" class="inline w-5 h-5 mr-2"></i>
                    Submit Ticket
                </button>
                <a href="{{ route('user.support') }}" 
                   class="px-6 py-3 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
@endsection