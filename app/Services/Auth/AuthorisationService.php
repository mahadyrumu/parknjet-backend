<?php

namespace App\Services\Auth;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthorisationService
{
    public function checkUserWithBearToken($userId)
    {
        return Auth::id() == $userId;
    }
}
