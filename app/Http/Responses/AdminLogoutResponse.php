<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;

class AdminLogoutResponse implements Responsable
{
    public function toResponse($request): RedirectResponse
    {
        // Redirect to home page instead of login page
        return redirect()->to('/');
    }
}