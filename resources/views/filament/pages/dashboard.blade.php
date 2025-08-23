<x-filament-panels::page>
    {{-- Theme Toggle (Alpine + localStorage) --}}
    <div
        x-data="{
            theme: localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
            init() {
                this.apply();
                // Sinkron dengan perubahan OS
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                    if (! localStorage.getItem('theme')) {
                        this.theme = e.matches ? 'dark' : 'light';
                        this.apply();
                    }
                });
            },
            toggle() {
                this.theme = (this.theme === 'dark') ? 'light' : 'dark';
                localStorage.setItem('theme', this.theme);
                this.apply();
            },
            apply() {
                const root = document.documentElement;
                if (this.theme === 'dark') {
                    root.classList.add('dark');
                } else {
                    root.classList.remove('dark');
                }
            }
        }"
        class="mb-4 flex justify-end"
    >
        <button
            type="button"
            @click="toggle()"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
            aria-label="Toggle dark mode"
            x-bind:aria-pressed="theme === 'dark'"
        >
            {{-- Icon: auto switch with x-show --}}
            <svg x-show="theme !== 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 3v2m0 14v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M3 12h2m14 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42M12 8a4 4 0 100 8 4 4 0 000-8z"/>
            </svg>
            <svg x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor"
                 viewBox="0 0 24 24">
                <path
                    d="M21.64 13a9 9 0 01-11.31-11.31A9 9 0 1021.64 13z"/>
            </svg>
            <span x-text="theme === 'dark' ? 'Dark' : 'Light'"></span>
        </button>
    </div>

    @php
        use App\Models\Order;
        use App\Models\User;
        use App\Models\Game;
        use Carbon\Carbon;

        // Quick stats
        $todayRevenue = Order::whereDate('created_at', Carbon::today())
            ->whereIn('status', ['PAID', 'DELIVERED'])
            ->sum('total');

        $totalOrders = Order::whereDate('created_at', Carbon::today())->count();
        $newUsers = User::whereDate('created_at', Carbon::today())->count();
        $activeGames = Game::where('is_active', true)->count();
    @endphp

    {{-- Welcome Section --}}
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="fi-section-content p-6">
            <h2 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">
                Welcome to Admin Dashboard
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                Today is {{ Carbon::now()->format('l, d F Y') }}
            </p>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        {{-- Revenue Card --}}
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center space-x-2">
                <div class="flex-shrink-0">
                    <div class="rounded-lg bg-primary-50 p-3 dark:bg-primary-400/10">
                        <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Revenue</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Orders Card --}}
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center space-x-2">
                <div class="flex-shrink-0">
                    <div class="rounded-lg bg-success-50 p-3 dark:bg-success-400/10">
                        <svg class="h-6 w-6 text-success-600 dark:text-success-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Orders</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalOrders }}</p>
                </div>
            </div>
        </div>

        {{-- Users Card --}}
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center space-x-2">
                <div class="flex-shrink-0">
                    <div class="rounded-lg bg-warning-50 p-3 dark:bg-warning-400/10">
                        <svg class="h-6 w-6 text-warning-600 dark:text-warning-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">New Users Today</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $newUsers }}</p>
                </div>
            </div>
        </div>

        {{-- Games Card --}}
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center space-x-2">
                <div class="flex-shrink-0">
                    <div class="rounded-lg bg-info-50 p-3 dark:bg-info-400/10">
                        <svg class="h-6 w-6 text-info-600 dark:text-info-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 01-.657.643 48.39 48.39 0 01-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 01-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 00-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 01-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 00.657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 005.427-.63 48.05 48.05 0 00.582-4.717.532.532 0 00-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 00.658-.663 48.422 48.422 0 00-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 01-.61-.58v0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Games</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $activeGames }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Widgets --}}
    <div class="mt-6">
        <div class="rounded-xl bg-white p-2 ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            @livewire(\App\Filament\Widgets\RecentOrders::class)
        </div>
    </div>
</x-filament-panels::page>
