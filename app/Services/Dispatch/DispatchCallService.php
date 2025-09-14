<?php

namespace App\Services\Dispatch;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Twilio\TwiML\VoiceResponse;

class DispatchCallService
{
    function dispatch_greets($request)
    {
        $response = new VoiceResponse();

        $caller_id = "";
        if (isset($request->From)) {
            $caller_id = $request->From;
            Session::put('dispatch_data_phone', $caller_id);
        } else {
            $caller_id = Session::get('dispatch_data_phone');
        }
        $lotPhoneNo = "+12062418800"; // Lot1
        $dialedNo = "+12065671610";
        if (isset($request->To)) {
            $dialedNo = $request->To;
        } 
        if($dialedNo == "+12065671800"){
            $lotPhoneNo = "+12062448400"; // Lot2
        }
        Log::info("Phone Number : " . $caller_id);

        $name = "Hello ";
        if (isset(people[$caller_id]) && $name .= people[$caller_id]) {
            Session::put('dispatch_data_name', $name);
        } else {
            Session::put('dispatch_data_name', $name);
        }
        Log::info("Name : " . $name);

        $response->say($name, ['voice' => 'woman']);
        $gather = $response->gather(['numDigits' => 1, 'action' => route('twilio.main_menu'), 'method' => 'POST']);
        $gather->say('Thank you for calling PARK N JET Airport Parking .
        For pickup service from the airport, Press 1 .
        For driving directions to Park N Jet One, Press 2 .
        For driving directions to Park N Jet Two, Press 3 .

        To Speak to an operator, Press 0 .
        To repeat the current menu, Press 9 .
        Press any other key to start over.
        Or Just stay on the line to speak to an operator .', ['voice' => 'woman']);

        $response->dial($lotPhoneNo);
        $response->say(" Goodbye.", ['voice' => 'woman']);

        return $response;
    }

    function dispatch_main_menu($request)
    {
        Log::info("dispatch_main_menu request");
        Log::info($request);
        $name = "";
        if (Session::has('dispatch_data_name')) {
            $name = Session::get('dispatch_data_name');
        }
        Log::info("Name : " . $name);
        
        $lotPhoneNo = "+12062418800"; // Lot1
        $dialedNo = "+12065671610";
        if (isset($request->To)) {
            $dialedNo = $request->To;
        } 
        if($dialedNo == "+12065671800"){
            $lotPhoneNo = "+12062448400"; // Lot2
        }

        $digits = "1";
        if (isset($request->Digits)) {
            $digits = $request->Digits;
        }
        Log::info("Digits : " . $digits);
        $response = new VoiceResponse();

        switch ($digits) {
            case '0':
                $response->dial($lotPhoneNo);
                $response->say(" Goodbye.", ['voice' => 'woman']);
                break;
            case '1':
                $gather = $response->gather(['numDigits' => 5, 'action' => route('twilio.dispatch_menu', ['menu' => 1]), 'method' => 'POST']);
                $gather->say($name . ", Welcome back to Seattle Tacoma International Airport. Thank you for using our Quicker and Faster, Automated airport pickup service. Please enter your 5 digit claim I D number .", ['voice' => 'woman']);
                break;
            case '2':
                $gather = $response->gather(['numDigits' => 1, 'action' => route('twilio.directions', ['direction' => 2]), 'method' => 'POST']);
                $gather->say(" Our Address is 1 8 2 2 0. 8th avenue South in Sea Tac Washington.
            For directions from Interstate 5, Press 1 .
            For directions from Interstate 4 O 5 Press 2 .
            For directions from Interstate 5 O 9, Press 3 .
            For directions from Sea Tac International Airport Press 4 .
            To repeat the current menu, Press 9 .
            For the Previous menu, Press * . ", ['voice' => 'woman']);
                break;
            case '3':
                $gather = $response->gather(['numDigits' => 1, 'action' => route('twilio.directions', ['direction' => 3]), 'method' => 'POST']);
                $gather->say(" Our Address is 1 2 4 4. South 140th street in Seattle, Washington.
            For directions from Interstate 5, Press 1 .
            For directions from Interstate 4 O 5 Press 2 .
            For directions from Interstate 5 1 8 West, Press 3 .
            For directions from Sea Tac International Airport Press 4 .
            To repeat the current menu, Press 9 .
            For the Previous menu, Press * . ", ['voice' => 'woman']);
                break;
            case '9':
                $response->redirect(route('twilio.greet'));
                break;
            default:
                $response->redirect(route('twilio.greet'));
                break;
        }

        return $response;
    }

    function dispatch_directions($request)
    {
        Log::info("dispatch_directions request");
        Log::info($request);
        $digits = "1";
        if (isset($request->Digits)) {
            $digits = $request->Digits;
        }
        Log::info("Digits : " . $digits . " Direction : " . $request->direction);

        $response = new VoiceResponse();
        if ($request->direction == '2') {
            switch ($digits) {
                case '1':
                    $response->say(" Take exit 152. 
                    Then Follow 188th street. 2 miles (west) .
                    Continue thru the tunnel . 
                    Turn left on 8th Avenue South . 
                    (which is the second traffic light after the tunnel) . 
                    We are located on the left side . ", ['voice' => 'woman']);
                    $gather = $response->gather(['numDigits' => 1, 'action' => route('twilio.directions', ['direction' => 2]), 'method' => 'POST']);
                    $gather->say(" If you want to listen to the same directions again, Press 1 .
                    For directions from Interstate 4 O 5 Press 2 .
                    For directions from Interstate 5 O 9, Press 3 .
                    For directions from Sea Tac International Airport Press 4 .
                    Press any other key to start over . ", ['voice' => 'woman']);
                    break;
                case '2':
                    $response->say(" Take the exit for I 5 South bound, continue for one mile .
                    take exit 152 . 
                    Then Follow 188th street. 2 miles (west) .
                    Continue thru the tunnel . 
                    Turn left on 8th Avenue South . 
                    (which is the second traffic light after the tunnel) . 
                    We are located on the left side . ", ['voice' => 'woman']);
                    $gather = $response->gather(['numDigits' => 1, 'action' => route('twilio.directions', ['direction' => 2]), 'method' => 'POST']);
                    $gather->say(" If you want to listen to the same directions again Press 2 .
                    For directions from Interstate 5, Press 1 .
                    For directions from Interstate 5 O 9, Press 3 .
                    For directions from Sea Tac International Airport Press 4 .
                    Press any other key to start over . ", ['voice' => 'woman']);
                    break;
                case '3':
                    $response->say(" Take the Normandy Park Exit onto DES MOINES MEMORIAL Drive .
                    Turn left at 8th Avenue South . 
                    We are located on the left side . ", ['voice' => 'woman']);
                    $gather = $response->gather(['numDigits' => 1, 'action' => route('twilio.directions', ['direction' => 2]), 'method' => 'POST']);
                    $gather->say(" If you want to listen to the same directions again Press 3.
                    For directions from Interstate 5, Press 1.
                    For directions from Interstate 4 O 5 Press 2.
                    For directions from Sea Tac International Airport Press 4.
                    Press any other key to start over. ", ['voice' => 'woman']);
                    break;
                case '4':
                    $response->say(" Turn Right on International Boulevard and. 
                    Turn right on 188th Street. 
                    Drive 1 mile. 
                    Turn left on 8th Avenue South (the second traffic light after the tunnel). 
                    We are located on the left side. ", ['voice' => 'woman']);
                    $gather = $response->gather(['numDigits' => 1, 'action' => route('twilio.directions', ['direction' => 2]), 'method' => 'POST']);
                    $gather->say(" If you want to listen to same directions again Press 4.
                    For directions from Interstate 5, Press 1.
                    For directions from Interstate 4 O 5 Press 2.
                    For directions from Interstate 5 O 9, Press 3.
                    Press any other key to start over. ", ['voice' => 'woman']);
                    break;
                case '*':
                    $response->redirect(route('twilio.greet'));
                    break;
                case '9':
                    $response->redirect(route('twilio.main_menu', ['Digits' => 2]));
                    break;

                default:
                    $response->say(" Invalid Response. ", ['voice' => 'woman']);
                    $response->redirect(route('twilio.main_menu', ['Digits' => 2]));
                    break;
            }
        }
        if ($request->direction == '3') {
            switch ($digits) {
                case '1':
                    $response->say(" Take exit 152. 
                    Take exit 154B to merge onto WA-518 (west) toward Burien/Sea-Tac/Airport. 
                    Take the Des Moines Memorial Drive exit.
                    Turn right onto Des Moines Memorial Drive. 
                    Turn left onto S 140th Street. ParkNJet two is on the right.", ['voice' => 'woman']);
                    $gather = $response->gather(['numDigits' => 1, 'action' => route('twilio.directions', ['direction' => 3]), 'method' => 'POST']);
                    $gather->say(" If you want to listen to the same directions again, Press 1 .
                    For directions from Interstate 4 O 5 Press 2 .
                    For directions from Interstate 5 1 8 West, Press 3 .
                    For directions from Sea Tac International Airport Press 4 .
                    Press any other key to start over . ", ['voice' => 'woman']);
                    break;
                case '2':
                    $response->say(" Continue onto WA-518 West. 
                    Take the Des Moines Memorial Drive exit, 
                    Turn right onto Des Moines Memorial Drive. 
                    Turn left onto South 140th Street. ParkNJet two is on the right. ", ['voice' => 'woman']);
                    $gather = $response->gather(['numDigits' => 1, 'action' => route('twilio.directions', ['direction' => 3]), 'method' => 'POST']);
                    $gather->say(" If you want to listen to the same directions again Press 2 .
                    For directions from Interstate 5, Press 1 .
                    For directions from WA 5 1 8 West, Press 3 .
                    For directions from Sea Tac International Airport Press 4 .
                    Press any other key to start over . ", ['voice' => 'woman']);
                    break;
                case '3':
                    $response->say(" Take the Des Moines Memorial Drive exit, 
                    Turn right onto Des Moines Memorial Drive. 
                    Turn left onto S 140th St. 
                    ParkNJet-2 is on the right. ", ['voice' => 'woman']);
                    $gather = $response->gather(['numDigits' => 1, 'action' => route('twilio.directions', ['direction' => 3]), 'method' => 'POST']);
                    $gather->say(" If you want to listen to the same directions again Press 3.
                    For directions from Interstate 5, Press 1.
                    For directions from Interstate 4 O 5 Press 2.
                    For directions from Sea Tac International Airport Press 4.
                    Press any other key to start over. ", ['voice' => 'woman']);
                    break;
                case '4':
                    $response->say(" Keep right at the fork and merge onto Airport Expressway. 
                    Keep left at the fork and merge onto WA-518 West. 
                    Take the Des Moines Memorial Dr exit, 
                    Turn right onto Des Moines Memorial Dr. Turn left onto S 140th St. ParkNJet-2 is on the right. ", ['voice' => 'woman']);
                    $gather = $response->gather(['numDigits' => 1, 'action' => route('twilio.directions', ['direction' => 3]), 'method' => 'POST']);
                    $gather->say(" If you want to listen to same directions again Press 4.
                    For directions from Interstate 5, Press 1.
                    For directions from Interstate 4 O 5 Press 2.
                    For directions from WA 5 1 8 W, Press 3.
                    Press any other key to start over. ", ['voice' => 'woman']);
                    break;
                case '*':
                    $response->redirect(route('twilio.greet'));
                    break;
                case '9':
                    $response->redirect(route('twilio.main_menu', ['Digits' => 2]));
                    break;

                default:
                    $response->say(" Invalid Response. ", ['voice' => 'woman']);
                    $response->redirect(route('twilio.main_menu', ['Digits' => 2]));
                    break;
            }
        }
        return $response;
    }

    function dispatch_menu($request)
    {
        Log::info("dispatch_menu request");
        Log::info($request);
        $digits = "0";
        if (isset($request->Digits)) {
            $digits = $request->Digits;
        }

        $len = strlen($digits);
        $response = new VoiceResponse();
        if ($len != '5') {
            Log::info("Digits : " . $digits);
            $gather = $response->gather(['numDigits' => 5, 'timeout' => 20, 'action' => route('twilio.dispatch_menu', ['menu' => 1]), 'method' => 'POST']);
            $gather->say("enter your 5 digit claim I d number .", ['voice' => 'woman']);
            $response->say(" We didn't receive any input. Please try again.", ['voice' => 'woman']);
            $gather = $response->gather(['numDigits' => 5, 'timeout' => 20, 'action' => route('twilio.dispatch_menu', ['menu' => 1]), 'method' => 'POST']);
            $gather->say("Enter your 5 digit claim I d number .", ['voice' => 'woman']);
            $response->say(" If you are unable to find your claim I d number, please hang up, call again and Press 0 to reach an operator. Good bye. ", ['voice' => 'woman']);
            $response->Hangup();
        } else {
            Log::info("claimId : " . $digits);
            $arr = str_split($digits);
            Session::put('dispatch_data_claimId', $digits);

            $claimid = '';
            for ($i = 0; $i < $len; $i++) {
                $claimid .= $arr[$i] . " ,";
            }
            $gather = $response->gather(['numDigits' => 1, 'action' => route('twilio.confirm_claimid'), 'method' => 'POST']);
            $gather->say(" If your claim i d number is " . $claimid . " press 1. if incorrect, press 0 to re enter . ", ['voice' => 'woman']);
        }
        return $response;
    }

    function dispatch_confirm_claimid($request)
    {
        Log::info("dispatch_confirm_claimid request");
        Log::info($request);
        $digits = "1";
        if (isset($request->Digits)) {
            $digits = $request->Digits;
        }
        Log::info("Digits : " . $digits);

        $response = new VoiceResponse();
        switch ($digits) {
            case '0':
                $response->redirect(route('twilio.dispatch_menu', ['menu' => 0]));
                break;
            case '1':
                $gather = $response->gather(['numDigits' => 1, 'action' => route('twilio.choose_island'), 'method' => 'POST']);
                $gather->say(" If you are going towards, OR waiting at courtesy vehicles island 1, press 1.
                If you are going towards, OR waiting at courtesy vehicles island 3, press 3. ", ['voice' => 'woman']);
                break;

            default:
                $response->say(" Invalid Response. Please re enter your claim i d number . ", ['voice' => 'woman']);
                $response->redirect(route('twilio.dispatch_menu', ['menu' => 0]));
                break;
        }
        return $response;
    }

    function dispatch_choose_island($request)
    {
        Log::info("dispatch_choose_island request");
        Log::info($request);
        $digits = "6";
        if (isset($request->Digits)) {
            $digits = $request->Digits;
        }
        Session::put('dispatch_data_island', $digits);
        $response = new VoiceResponse();

        switch ($digits) {
            case '1':
            case '3':
                Log::info("Island : " . $digits);
                $phone = Session::get('dispatch_data_phone');
                if (!isset(port_phones[$phone])) {
                    $gather = $response->gather(['numDigits' => 1, 'action' => route('twilio.choose_delay'), 'method' => 'POST']);
                    $gather->say(" Please indicate how many minutes it will take for you to arrive at island " . $digits . ". Press a number from 0 to 9. 
                    0 means you are already at island " . $digits . ". 9 means that you will be at island " . $digits . " with in 9 minutes. 
                     ", ['voice' => 'woman']);
                } else {
                    $response->redirect(route('twilio.choose_delay', ['Digits' => 0]));
                }
                break;

            default:
                Log::info("Digits : " . $digits);
                $response->say(" Invalid Response. ", ['voice' => 'woman']);
                $response->redirect(route('twilio.confirm_claimid', ['Digits' => 1]));
                break;
        }
        $response->redirect(route('twilio.choose_delay', ['Digits' => 0]));
        return $response;
    }

    function dispatch_choose_delay($request)
    {
        Log::info("dispatch_choose_delay request");
        Log::info($request);
        $digits = "0";
        if (isset($request->Digits)) {
            $digits = $request->Digits;
        }
        Log::info("Delay : " . $digits);
        Session::put('dispatch_data_delay', $digits);
        $island = Session::get('dispatch_data_island');

        $response = new VoiceResponse();
        $response->say(" A vehicle has been dispatched to pick you up shortly . 
        Please wait at the beginning of island " . $island . " A to ensure prompt pickup .
        When you see a Park n Jet vehicle, Flag it, down. 
        Thank you for choosing Park N Jet . ", ['voice' => 'woman']);
        $response->Hangup();

        // Get data from Session
        $claim_id = Session::get('dispatch_data_claimId');
        $island = Session::get('dispatch_data_island');
        $delay = Session::get('dispatch_data_delay');
        $phone = $request->From;

        // Save the data
        $dispatchCommonService = new DispatchCommonService;
        $dispatch_data = $dispatchCommonService->getDispatchData($claim_id, 0, $island, $delay, $phone);

        Log::info($dispatch_data);

        $dispatchDBService = new DispatchDBService;
        $dispatchDBService->storeOrUpdate($claim_id, $dispatch_data);

        return $response;
    }
}
