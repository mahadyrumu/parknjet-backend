<?php

namespace App\Http\Controllers\PNJ\API\Payment;

use App\Exceptions\PNJException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentIntentRequest;
use App\Services\PaymentGateway\StripeCustomerService;
use App\Services\PaymentGateway\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
 

class StripeController extends Controller
{
    public function create_payment_intent(PaymentIntentRequest $request, StripeCustomerService $stripeCustomerService, StripePaymentService $stripePaymentService)
    {
        try {
            $customerId = '';
            $stripeCustomerDB = $stripeCustomerService->findByEmail($request->email, $request->lotType)->first();
            if ($stripeCustomerDB != null) {
                // if we have a customer already, no need to create again, return null
                $customerId = $stripeCustomerDB->customerId;
            } else {
                $newStripeCustomer = $stripeCustomerService->stripeCutomerCreate(111, $request->email, "Created for payment intent", $request->lotType);
                $stripeCustomerService->create($newStripeCustomer->id, $request->email, $request->lotType);
                $customerId = $newStripeCustomer->id;
            }

            Log::info("Lot ID: " . $request->lotType . ", Stripe customer : " . $customerId);

            if ($request->lotType == LotType['LOT_1']) {
                \Stripe\Stripe::setApiKey(env('LOT1_STRIPE_SECRET'));
            } elseif ($request->lotType == LotType['LOT_2']) {
                \Stripe\Stripe::setApiKey(env('LOT2_STRIPE_SECRET'));
            }

            $intent = $stripePaymentService->createIntent($customerId, $request->email, "Created for payment intent", $request->amount, $request->lotType);

            Log::info("client_secret : " . $intent->client_secret);

            return response()->json([
                'success' => true,
                'data' => $intent
            ], ResponseCode["Success"]);
        } catch (PNJException $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], ResponseCode["Forbidden"]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => true,
                'message' => "Sorry, something went wrong."
            ], ResponseCode["Forbidden"]);
        }
    }
}
