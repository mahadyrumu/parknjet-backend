<?php

namespace App\Http\Controllers\PNJ\Dispatch;

use App\Http\Controllers\Controller;
use App\Services\Dispatch\DispatchCallService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DispatchTwilioCall extends Controller
{
    private $dispatchCallService;

    public function __construct(
        DispatchCallService $dispatchCallService
    ) {
        $this->dispatchCallService = $dispatchCallService;
    }

    public function twilio_greet(Request $request)
    {
        Log::info("Call Dispatch twilio greet start");

        $validator = Validator::make($request->all(), [
            'From' => 'required',
        ]);

        if ($validator->fails()) {
            Log::error("Call Dispatch validation failed");
            Log::error("Call Dispatch End");
            return response()->json([
                'error' => true,
                "message" => $validator->errors()
            ], 401);
        }
        try {

            Log::info("Call Dispatch validation pass");
            $response = $this->dispatchCallService->dispatch_greets($request);
            return response($response)->header('Content-Type', 'text/xml');
        } catch (\Exception $e) {

            Log::error("Dispatch Error: " . $e->getMessage());
        }
        Log::info("Call Dispatch twilio greet end");
    }

    public function twilio_main_menu(Request $request)
    {
        Log::info("Call Dispatch twilio_main_menu Start");
        try {

            $response = $this->dispatchCallService->dispatch_main_menu($request);
            return response($response)->header('Content-Type', 'text/xml');
        } catch (\Exception $e) {

            Log::error("Dispatch Error: " . $e->getMessage());
        }
        Log::info("Call Dispatch twilio_main_menu End");
    }

    public function twilio_directions(Request $request)
    {
        Log::info("Call Dispatch twilio_directions Start");
        try {

            $response = $this->dispatchCallService->dispatch_directions($request);
            return response($response)->header('Content-Type', 'text/xml');
        } catch (\Exception $e) {

            Log::error("Dispatch Error: " . $e->getMessage());
        }
        Log::info("Call Dispatch twilio_directions End");
    }

    public function twilio_dispatch_menu(Request $request)
    {
        Log::info("Call Dispatch twilio_dispatch_menu Start");
        try {

            $response = $this->dispatchCallService->dispatch_menu($request);
            return response($response)->header('Content-Type', 'text/xml');
        } catch (\Exception $e) {

            Log::error("Dispatch Error: " . $e->getMessage());
        }
        Log::info("Call Dispatch twilio_dispatch_menu End");
    }

    public function twilio_confirm_claimid(Request $request)
    {
        Log::info("Call Dispatch twilio_confirm_claimid Start");
        try {

            $response = $this->dispatchCallService->dispatch_confirm_claimid($request);
            return response($response)->header('Content-Type', 'text/xml');
        } catch (\Exception $e) {

            Log::error("Dispatch Error: " . $e->getMessage());
        }
        Log::info("Call Dispatch twilio_confirm_claimid End");
    }

    public function twilio_choose_island(Request $request)
    {
        Log::info("Call Dispatch twilio_choose_island Start");
        try {

            $response = $this->dispatchCallService->dispatch_choose_island($request);
            return response($response)->header('Content-Type', 'text/xml');
        } catch (\Exception $e) {

            Log::error("Dispatch Error: " . $e->getMessage());
        }
        Log::info("Call Dispatch twilio_choose_island End");
    }

    public function twilio_choose_delay(Request $request)
    {
        Log::info("Call Dispatch twilio_choose_delay Start");
        try {

            $response = $this->dispatchCallService->dispatch_choose_delay($request);
            return response($response)->header('Content-Type', 'text/xml');
        } catch (\Exception $e) {

            Log::error("Dispatch Error: " . $e->getMessage());
        }
        Log::info("Call Dispatch twilio_choose_delay End");
    }
}
