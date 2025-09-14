<?php

namespace App\Http\Controllers\PNJ\Dispatch;

use App\Http\Controllers\Controller;
use App\Services\Dispatch\DispatchSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DispatchTwilioSms extends Controller
{
    private $dispatchSmsService;

    public function __construct(
        DispatchSMSService $dispatchSmsService
    ) {
        $this->dispatchSmsService = $dispatchSmsService;
    }

    public function twilio_sms(Request $request)
    {
        Log::info(
            "SMS Dispatch Start for phone number " . $request->From . " and info message " . $request->Body
        );

        $validator = Validator::make($request->all(), [
            'From' => 'required',
            'Body' => 'required',
        ]);

        if ($validator->fails()) {
            Log::info("SMS Dispatch validation failed");
            return response()->json([
                'error' => true,
                "message" => $validator->errors()
            ], 401);
        }
        try {

            $this->dispatchSmsService->dispatch_send_sms($request);
        } catch (\Exception $e) {

            Log::error("Dispatch Error: " . $e->getMessage());
            return response()->json([
                'error' => true,
                "message" => $e->getMessage()
            ], 401);
        }
        Log::info("SMS Dispatch End");
    }
}
