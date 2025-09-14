<?php

namespace App\Services\PaymentGateway;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StripePaymentService
{
    public function createIntent($customerId, $email, $description, $amount, $lotType)
    {
        Log::info("Creating payment Intent with email " . $email . " customerId : " . $customerId);
        $stripeCustomerService = new StripeCustomerService;
        try {
            return \Stripe\PaymentIntent::create([
                'customer' => $customerId,
                'amount' => $amount,
                'currency' => 'usd',
                'automatic_payment_methods' => [
                    'enabled' => true, // Enable automatic payment methods
                ],
            ]);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            if (Str::contains($ex, "No such customer")) {
                Log::info("Creating new customer");
                $newStripeCustomer = $stripeCustomerService->stripeCutomerCreate(111, $email, $description, $lotType);
                $customerId = $newStripeCustomer->id;
                $customerIds = $stripeCustomerService->findByEmail($email, $lotType)->get();
                foreach ($customerIds as $key => $customer) {
                    $customer->isDeleted = 1;
                    $customer->update();
                }
                $stripeCustomerService->create($customerId, $email, $lotType);
                return $this->createIntent($customerId, $email, $description, $amount, $lotType);
            }
        }
    }
}
