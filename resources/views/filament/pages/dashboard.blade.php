<x-filament-panels::page>
    @php
        use Carbon\Carbon;
        use App\Models\Order;
        use App\Models\User;
        use App\Models\SupportTicket;

        // Waktu lokal Indonesia
        $tz = 'Asia/Jakarta';
        $nowLocal = Carbon::now($tz);
        $todayStartUtc     = $nowLocal->copy()->startOfDay()->clone()->setTimezone('UTC');
        $todayEndUtc       = $nowLocal->copy()->endOfDay()->clone()->setTimezone('UTC');
        $yesterdayStartUtc = $nowLocal->copy()->subDay()->startOfDay()->clone()->setTimezone('UTC');
        $yesterdayEndUtc   = $nowLocal->copy()->subDay()->endOfDay()->clone()->setTimezone('UTC');

        // Aggregates (hindari query berulang di Blade)
        $todayRevenue = Order::whereBetween('created_at', [$todayStartUtc, $todayEndUtc])
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->sum('total');

        $yesterdayRevenue = Order::whereBetween('created_at', [$yesterdayStartUtc, $yesterdayEndUtc])
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->sum('total');

        $percentageChange = $yesterdayRevenue > 0
            ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100
            : 0;

        $todayOrdersCount = Order::whereBetween('created_at', [$todayStartUtc, $todayEndUtc])->count();
        $todayPendingCount = Order::whereBetween('created_at', [$todayStartUtc, $todayEndUtc])->where('status', 'PENDING')->count();

        $newUsersToday = User::whereBetween('created_at', [$todayStartUtc, $todayEndUtc])->count();
        $totalUsers = User::count();

        $openTickets = SupportTicket::whereIn('status', ['open', 'pending'])->count();
        $urgentTickets = SupportTicket::where('priority', 'urgent')->whereIn('status', ['open', 'pending'])->count();

        $recentOrders = Order::latest()->limit(5)->get();
    @endphp

    {{-- Welcome Section --}}
    <x-filament::section>
        <div class="text-center py-8">
            <h2 class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                Welcome to Takapedia Admin Dashboard
            </h2>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Manage your game top-up platform efficiently
            </p>
            <div class="mt-4 text-sm text-gray-500 dark:text-gray-500">
                {{ $nowLocal->locale('id')->translatedFormat('l, d F Y H:i') }} WIB
            </div>
        </div>
    </x-filament::section>

    {{-- Quick Stats Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Today's Revenue --}}
        <x-filament::card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Revenue</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        Rp {{ number_format($todayRevenue, 0, ',', '.') }}
                    </p>
                    <p class="text-xs {{ $percentageChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $percentageChange >= 0 ? '↑' : '↓' }} {{ abs(round($percentageChange, 1)) }}% from yesterday
                    </p>
                </div>
                <div class="p-3 bg-primary-100 dark:bg-primary-900 rounded-full">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </x-filament::card>

        {{-- Today's Orders --}}
        <x-filament::card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Orders</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        {{ $todayOrdersCount }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ $todayPendingCount }} pending
                    </p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </div>
        </x-filament::card>

        {{-- New Users --}}
        <x-filament::card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">New Users Today</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        {{ $newUsersToday }}
                    </p>
                    <p class="text-xs text-gray-500">
                        Total: {{ $totalUsers }} users
                    </p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </x-filament::card>

        {{-- Support Tickets --}}
        <x-filament::card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Open Tickets</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        {{ $openTickets }}
                    </p>
                    <p class="text-xs text-orange-600">
                        {{ $urgentTickets }} urgent
                    </p>
                </div>
                <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-full">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                </div>
            </div>
        </x-filament::card>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Revenue Chart Widget --}}
        <x-filament::card>
            <h3 class="text-lg font-semibold mb-4">Revenue Last 7 Days</h3>
            @livewire(\App\Filament\Widgets\RevenueChart::class)
        </x-filament::card>

        {{-- Popular Games Widget --}}
        <x-filament::card>
            <h3 class="text-lg font-semibold mb-4">Top Selling Games</h3>
            @livewire(\App\Filament\Widgets\PopularGames::class)
        </x-filament::card>
    </div>

    {{-- Recent Activity Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Orders --}}
        <x-filament::card>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Recent Orders</h3>
                <a href="{{ route('filament.admin.resources.orders.index') }}" class="text-sm text-primary-600 hover:text-primary-700">
                    View All →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($recentOrders as $order)
                            <tr>
                                <td class="px-3 py-2 text-sm">{{ $order->invoice_no }}</td>
                                <td class="px-3 py-2 text-sm">
                                    {{ \Illuminate\Support\Str::limit($order->email, 20) }}
                                </td>
                                <td class="px-3 py-2 text-sm">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td class="px-3 py-2">
                                    @php
                                        $badge = match ($order->status) {
                                            'DELIVERED' => 'bg-green-100 text-green-800',
                                            'PAID'      => 'bg-blue-100 text-blue-800',
                                            'PENDING'   => 'bg-yellow-100 text-yellow-800',
                                            default     => 'bg-red-100 text-red-800',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs rounded-full {{ $badge }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::card>

        {{-- (Contoh) Kotak lain di samping Recent Orders, bisa isi aktivitas lain --}}
        <x-filament::card>
            <h3 class="text-lg font-semibold mb-4">Notes</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tambahkan widget lain di sini bila perlu.</p>
        </x-filament::card>
    </div>
</x-filament-panels::page>
