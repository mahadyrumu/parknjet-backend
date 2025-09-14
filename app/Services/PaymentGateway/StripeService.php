<?php

namespace App\Services\PaymentGateway;

use App\Exceptions\PNJException;
use Illuminate\Support\Facades\Log;
use Payment;

class StripeService
{
    public function chargeCreditCardThroughStripeForReservation($amountToBeCharged, $stripeToken, $reservation)
    {
        $customerId = $this->createCustomer($stripeToken, $reservation);
        $charge = '';

        Log::info("Charging amount " . $amountToBeCharged . " to customer credit card with stripe token " . $stripeToken . " through stripe !");
        if ($reservation->lotType == LotType['LOT_1']) {
            \Stripe\Stripe::setApiKey(env('LOT1_STRIPE_SECRET'));
        } elseif ($reservation->lotType == LotType['LOT_2']) {
            \Stripe\Stripe::setApiKey(env('LOT2_STRIPE_SECRET'));
        }
        try {
            if ($customerId != null) {
                $charge = \Stripe\Charge::create([
                    "amount" => $amountToBeCharged * 100,
                    "currency" => "usd",
                    "customer" => $customerId,
                    "description" => "Reservation Id = " . $reservation->id . " and LotId = " . $reservation->lotType,
                ]);
            } else {
                $charge = \Stripe\Charge::create([
                    "amount" => $amountToBeCharged * 100,
                    "currency" => "usd",
                    "card" => $stripeToken,
                    "description" => "Reservation Id = " . $reservation->id . " and LotId = " . $reservation->lotType,
                ]);
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            try {
                $charge = \Stripe\Charge::create([
                    "amount" => $amountToBeCharged * 100,
                    "currency" => "usd",
                    "card" => $stripeToken,
                    "description" => "Reservation Id = " . $reservation->id . " and LotId = " . $reservation->lotType,
                ]);
            } catch (\Exception $exception) {
                Log::error($exception->getMessage());
                throw new PNJException($exception->getMessage());
            }
        }
        Log::info("Stripe Payment successful charge id = " . $charge->id);
        return $charge;
    }

    public function chargeCreditCardThroughStripeForPackage($amountToBeCharged, $stripeToken, $prePaidPackage)
    {
        $charge = '';

        Log::info("Charging amount " . $amountToBeCharged . " to customer credit card with stripe token " . $stripeToken . " through stripe !");
        if ($prePaidPackage->lotType == LotType['LOT_1']) {
            \Stripe\Stripe::setApiKey(env('LOT1_STRIPE_SECRET'));
        } elseif ($prePaidPackage->lotType == LotType['LOT_2']) {
            \Stripe\Stripe::setApiKey(env('LOT2_STRIPE_SECRET'));
        }
        try {
            $charge = \Stripe\Charge::create([
                "amount" => $amountToBeCharged * 100,
                "currency" => "usd",
                "card" => $stripeToken,
                "description" => "PrePaid Package Id = " . $prePaidPackage->id . " and LotId = " . $prePaidPackage->lotType,
            ]);
            Log::info("Stripe Payment successful charge id = " . $charge->id);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            throw new PNJException($exception->getMessage());
        }
        return $charge;
    }

    public function checkPaymentMethodThroughStripeForReservation($paymentId, $reservation)
    {
        try {
            if ($reservation->lotType == LotType['LOT_1']) {
                $stripe = new \Stripe\StripeClient(env('LOT1_STRIPE_SECRET'));
            } elseif ($reservation->lotType == LotType['LOT_2']) {
                $stripe = new \Stripe\StripeClient(env('LOT2_STRIPE_SECRET'));
            }
            Log::info("Stripe Payment retrieve for reservation payment Methods id = " . $paymentId);
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentId, []);

            if ($paymentIntent) {

                $paymentMethodsUpdate = $stripe->paymentIntents->update($paymentId, [
                    "description" => "Reservation Id = " . $reservation->id . " and LotId = " . $reservation->lotType,
                    'expand' => ['latest_charge']
                ]);
            }
            return $paymentMethodsUpdate['latest_charge'];
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            throw new PNJException($exception->getMessage());
        }
    }

    public function checkPaymentMethodThroughStripeForPricing($paymentId, $lotType)
    {
        try {
            if ($lotType == LotType['LOT_1']) {
                $stripe = new \Stripe\StripeClient(env('LOT1_STRIPE_SECRET'));
            } elseif ($lotType == LotType['LOT_2']) {
                $stripe = new \Stripe\StripeClient(env('LOT2_STRIPE_SECRET'));
            }
            Log::info("Stripe Payment retrieve for reservation payment Methods id = " . $paymentId);
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentId, []);
            $clientTotal = $paymentIntent->amount / 100;
            return $clientTotal;
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            throw new PNJException($exception->getMessage());
        }
    }

    public function checkPaymentMethodThroughStripeForPackage($paymentId, $prePaidPackage)
    {
        try {
            if ($prePaidPackage->lotType == LotType['LOT_1']) {
                $stripe = new \Stripe\StripeClient(env('LOT1_STRIPE_SECRET'));
            } elseif ($prePaidPackage->lotType == LotType['LOT_2']) {
                $stripe = new \Stripe\StripeClient(env('LOT2_STRIPE_SECRET'));
            }
            Log::info("Stripe Payment retrieve for package payment Methods id = " . $paymentId);
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentId, []);

            if ($paymentIntent) {

                $paymentMethodsUpdate = $stripe->paymentIntents->update($paymentId, [
                    "description" => "Package Id = " . $prePaidPackage->id . " and LotId = " . $prePaidPackage->lotType,
                    'expand' => ['latest_charge']
                ]);
            }
            return $paymentMethodsUpdate['latest_charge'];
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            throw new PNJException($exception->getMessage());
        }
    }

    public function chargeStoredCreditCardThroughStripe($lotType, $amountToBeCharged, $stripeCustomerId, $cardId, $userName, $reservationId)
    {
        if ($lotType == LotType['LOT_1']) {
            \Stripe\Stripe::setApiKey(env('LOT1_STRIPE_SECRET'));
        } elseif ($lotType == LotType['LOT_2']) {
            \Stripe\Stripe::setApiKey(env('LOT2_STRIPE_SECRET'));
        }
        $charge = \Stripe\Charge::create([
            "amount" => $amountToBeCharged * 100,
            "currency" => "usd",
            "customer" => $stripeCustomerId,
            "card" => $cardId,
            "description" => "Reservation Id = " . $reservationId,
            'metadata' => ['userName' => $userName]
        ]);
        Log::info("Stripe Payment successful charge id = " . $charge->id);
        return $charge;
    }

    public function createCustomer($stripeToken, $reservation)
    {
        Log::info("Creating stripe customer");
        $customer = null;
        $email = null;
        try {

            $email = $reservation->driver == null ? null : $reservation->driver->email;

            if ($stripeToken == null || $email == null) {
                // if we dont have stripe token or email, return null nothing to do
                return null;
            }
            $stripeCustomerService = new StripeCustomerService;
            $stripeCustomerDB = $stripeCustomerService->findByEmail($email, $reservation->lotType)->first();
            if ($stripeCustomerDB != null) {
                // if we have a customer already, no need to create again, return null
                return $stripeCustomerDB->customerId;
            }
            $customer = $stripeCustomerService->stripeCutomerCreate($stripeToken, $email, "Created along with reservation [" . $reservation->id . "] online payment progress", $reservation->lotType);
            Log::info("stripe customer");
            $stripeCustomerService->create($customer->id, $email, $reservation->lotType);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
        return $customer == null ? null : $customer->id;
    }

    public function refundPaymentThroughStripe($reservationDB, $paymentDB)
    {
        if ($paymentDB !== null) {
            Log::info("Refunding payment with id " . $paymentDB->id);
            if ($paymentDB->paymentStatus == Payment::PaymentStatus['PAID']) {
                if ($reservationDB->lotType == LotType['LOT_1']) {
                    \Stripe\Stripe::setApiKey(env('LOT1_STRIPE_SECRET'));
                    $stripe = new \Stripe\StripeClient(env('LOT1_STRIPE_SECRET'));
                } elseif ($reservationDB->lotType == LotType['LOT_2']) {
                    \Stripe\Stripe::setApiKey(env('LOT2_STRIPE_SECRET'));
                    $stripe = new \Stripe\StripeClient(env('LOT2_STRIPE_SECRET'));
                }
                $metaData = array("Reservation Id" => $reservationDB->id);
                $ch = null;
                try {
                    $ch = \Stripe\Charge::retrieve($paymentDB->stripeChargeId);
                } catch (\Exception $e) {
                    Log::error("Could not find charge id in Lot2. This could be in older stripe system");
                    \Stripe\Stripe::setApiKey(env('LOT1_STRIPE_SECRET'));
                    $stripe = new \Stripe\StripeClient(env('LOT1_STRIPE_SECRET'));
                    $ch = \Stripe\Charge::retrieve($paymentDB->stripeChargeId);
                }

                $refund = $stripe->refunds->create(
                    [
                        "charge" => $ch->id,
                        "metadata" => $metaData
                    ]
                );

                $paymentDB->paymentStatus = Payment::PaymentStatus['REFUNDED'];
                Log::info("Refund for payment with id " . $paymentDB->id . " successfully with refund id !" . $refund->id);
            } else {
                throw new PNJException("Payment status is NOT PAID, Cannot refund non paid transaction");
            }
        }
    }

    public function getCardType($stripeCharge)
    {
        $cardType = '';
        if ($stripeCharge) {
            if ($stripeCharge['card'] && $stripeCharge['card']['brand']) {
                $cardType = $stripeCharge['card']['brand'];
            } elseif ($stripeCharge['source'] && $stripeCharge['source']['brand']) {
                $cardType = $stripeCharge['source']['brand'];
            } elseif ($stripeCharge['payment_method_details'] && $stripeCharge['payment_method_details']['card'] && $stripeCharge['payment_method_details']['card']['brand']) {
                $cardType = $stripeCharge['payment_method_details']['card']['brand'];
            } else {
                # code...
            }
        }
        return $cardType;
    }
}
