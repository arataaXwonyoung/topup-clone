<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Profile Information -->
        <div>
            <form wire:submit="updateProfile">
                {{ $this->profileForm }}
                
                <div class="mt-6">
                    <x-filament::button type="submit">
                        Update Profile
                    </x-filament::button>
                </div>
            </form>
        </div>
        
        <!-- Update Password -->
        <div>
            <form wire:submit="updatePassword">
                {{ $this->passwordForm }}
                
                <div class="mt-6">
                    <x-filament::button type="submit">
                        Update Password
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>