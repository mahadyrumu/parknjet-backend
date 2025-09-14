<?php

namespace App\Services\Dispatch;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class DispatchSmsService
{
    function dispatch_send_sms($request)
    {
        $errors = 0;
        $errorTxt = "";
        $errorMsg = "";
        $values = [];
        $msg = $request->Body;
        $msg = str_replace("claimid:","",$msg);
        $msg = str_replace("claimid","",$msg);
        $msg = str_replace("claim:","",$msg);
        $msg = str_replace("claim","",$msg);
        $msg = str_replace("island:","",$msg);
        $msg = str_replace("island","",$msg);
        $msg = str_replace("isl","",$msg);
        $msg = str_replace("minutes:","",$msg);
        $msg = str_replace("min:","",$msg);
        $msg = str_replace("minutes","",$msg);
        $msg = str_replace("min","",$msg);
        $values = preg_split("/[\s,\*:|-]+/", $msg);

        $val_count = count($values);
        $claim_id = trim($values[0]);
        $island = 1;
        $delay = 0;
        $lot_id = 2;
        $to = $request->From;

        $claim_id = preg_replace("/[-]+/", "", $claim_id);
        $claim_id = (int) $claim_id;

        Log::debug("claim_id = " . (int) $claim_id);
        Log::debug("claim_id = " . $claim_id);

        if ($val_count >= 2) {
            $island = intval($values[1]);
        } else {
            $island = 0;
        }

        if ($val_count >= 3) {
            $delay = intval($values[2]);
        } else {
            $delay = -1;
        }

        // claim_id should be 5 digits.
        if ($claim_id >= 50000 && $claim_id < 100000) {
            $lot_id = 2;
        } else if ($claim_id >= 1000 && $claim_id < 50000) {
            $lot_id = 1;
        } else {
            $errors++;
            $errorTxt .= "Claim Id:(1000 - 99999) ";
            $errorMsg .= "\r\n" . $errorTxt;
        }

        // island value should be 1 or 3.
        if (!($island == 1 || $island == 3)) {
            $errors++;
            $errorTxt .= "Island:(1 or 3) ";
            $errorMsg .= "\r\n" . $errorTxt;
        }

        // delay should be 0 to 9 minutes.
        if ($delay > 9 or $delay < 0) {
            $errors++;
            $errorTxt .= "Minutes:(0 to 9) ";
            $errorMsg .= "\r\n" . $errorTxt;
        }

        if ($errors > 0) {
            $body = "Invalid Syntax" . $errorMsg . "\r\n Try again with correct syntax";
            $bodyLog = $msg . " Invalid Syntax " . $errorTxt . " Try again with correct syntax";
            $this->twilioSmsSend($to, $body);
            Log::error($bodyLog);
        } else {
            $dispatchCommonService = new DispatchCommonService;
            $dispatch_data = $dispatchCommonService->getDispatchData($claim_id, 0, $island, $delay, $to);

            // Check the claim is exist then update or insert
            $dispatchDBService = new DispatchDBService;
            $dispatchDBService->storeOrUpdate($claim_id, $dispatch_data);

            $body = "Shuttle has been dispatched to pick you up shortly. Please wait at the beginning of island " . $island . " and raise hand when you see Park N Jet shuttle";
            $this->twilioSmsSend($to, $body);
            Log::info("Dispatch sms to " . $to);
        }
    }

    public function twilioSmsSend($to, $body)
    {
        $client = new Client(env("TWILIO_SID"), env("TWILIO_TOKEN"));
        return $client->messages->create(
            $to,
            [
                'from' => env("TWILIO_FROM"),
                'body' => $body
            ]
        );
    }
}
