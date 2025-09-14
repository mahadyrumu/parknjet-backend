<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotFoundException extends Exception
{
    protected $message;

    public function __construct($message = '')
    {
        $this->message = $message;
        parent::__construct($message);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->message
        ]);
    }
}
