<?php

namespace App\Traits;

use App\Models\Backend\MemUser;
use Illuminate\Support\Facades\DB;

trait CheckUser
{

    public function checkEmail($email)
    {
        return MemUser::where("user_name", $email)->first();
    }

    public function checkEmailForPasswordReset($email)
    {
        return DB::connection('backend_mysql')->table('password_resets')->where('email', $email);
    }

    public function checkTokenForPasswordReset($email, $token)
    {
        return DB::connection('backend_mysql')->table('password_resets')->where([
            ['email', $email],
            ['token', $token],
        ]);
    }

    public function insertPasswordReset($email, $token)
    {
        return DB::connection('backend_mysql')->table('password_resets')
            ->insert(
                [
                    'email' => $email,
                    'token' => $token
                ]
            );
    }
}
