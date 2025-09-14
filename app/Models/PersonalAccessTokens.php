<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken;

class PersonalAccessTokens extends PersonalAccessToken
{
    protected $connection = 'backend_mysql';
}