<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Settings';
    protected static string $view = 'filament.pages.profile';
    protected static ?int $navigationSort = 99;
    
    public ?array $profileData = [];
    public ?array $passwordData = [];
    
    public function mount(): void
    {
        $this->profileData = [
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'phone' => auth()->user()->phone,
            'whatsapp' => auth()->user()->whatsapp,
        ];
    }
    
    public function profileForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile Information')
                    ->description('Update your account\'s profile information.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('whatsapp')
                            ->tel()
                            ->maxLength(20),
                    ])
            ])
            ->statePath('profileData');
    }
    
    public function passwordForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Update Password')
                    ->description('Ensure your account is using a strong password.')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->password()
                            ->required()
                            ->currentPassword(),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required()
                            ->rule(Password::default())
                            ->different('current_password'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->required()
                            ->same('password'),
                    ])
            ])
            ->statePath('passwordData');
    }
    
    public function updateProfile(): void
    {
        $data = $this->profileForm->getState();
        
        auth()->user()->update($data);
        
        Notification::make()
            ->success()
            ->title('Profile updated')
            ->body('Your profile information has been updated successfully.')
            ->send();
    }
    
    public function updatePassword(): void
    {
        $data = $this->passwordForm->getState();
        
        auth()->user()->update([
            'password' => Hash::make($data['password']),
        ]);
        
        $this->passwordForm->fill([]);
        
        Notification::make()
            ->success()
            ->title('Password updated')
            ->body('Your password has been updated successfully.')
            ->send();
    }
    
    protected function getForms(): array
    {
        return [
            'profileForm',
            'passwordForm',
        ];
    }
}