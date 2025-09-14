<?php

namespace App\Services\PaymentGateway;

use App\Models\Backend\StripeCustomer;
use App\Traits\SequenceUpdate;

class StripeCustomerService
{
    use SequenceUpdate;
    
    public function findByEmail($email, $lotType)
    {
        return StripeCustomer::where('email', $email)
            ->where('lotType', $lotType)
            ->where('isDeleted', 0);
    }

    public function stripeCutomerCreate($stripeToken, $email, $description, $lotType)
    {
        if ($lotType == LotType['LOT_1']) {
            $stripe = new \Stripe\StripeClient(env('LOT1_STRIPE_SECRET'));
        } elseif ($lotType == LotType['LOT_2']) {
            $stripe = new \Stripe\StripeClient(env('LOT2_STRIPE_SECRET'));
        }
        
        if ($stripeToken == 111) {
            return $stripe->customers->create([
                'email' => $email,
                'description' => $description,
            ]);
        } else {
            return $stripe->customers->create([
                'source' => $stripeToken,
                'email' => $email,
                'description' => $description,
            ]);
        }
    }

    public function create($id, $email, $lotType)
    {
        $stripeCustomer = new StripeCustomer;
        $stripeCustomer->customerId = $id;
        $stripeCustomer->email = $email;
        $stripeCustomer->lotType = $lotType;
        $stripeCustomer->save();
        $this->updateSequence('backend_mysql', 'stripe_customer', 'StripeCustomer_SEQ');
        return $stripeCustomer;
    }

    public function findCustomerIdOnStripe($customerId, $lotType)
    {
        if ($lotType == LotType['LOT_1']) {
            $stripe = new \Stripe\StripeClient(env('LOT1_STRIPE_SECRET'));
        } elseif ($lotType == LotType['LOT_2']) {
            $stripe = new \Stripe\StripeClient(env('LOT2_STRIPE_SECRET'));
        }

        return $stripe->customers->retrieve($customerId, []);
    }
}
