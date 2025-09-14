<?php

namespace App\Services\Pricing;
use App\Models\Backend\AnonPayment;
use App\Services\PaymentGateway\StripeService;
use App\Traits\SequenceUpdate;
use Payment;

class AnonPaymentService
{
    use SequenceUpdate;
    
    public function makeAnonPayment($pricing, $payment, $reservation)
    {
        $stripeService = new StripeService;
        if ($payment->paymentId) {
            $stripeCharge = $stripeService->checkPaymentMethodThroughStripeForReservation($payment->paymentId, $reservation);
        } else {
            $stripeCharge = $stripeService->chargeCreditCardThroughStripeForReservation($pricing['total'], $payment->stripeToken, $reservation);
        }
        return $this->createAnonPayment($pricing, $stripeCharge, Payment::PaymentStatus['PAID'], strtoupper($stripeCharge['payment_method_details']['card']['brand']));
    }
    
    public function createAnonPayment($pricing, $charge, $paymentStatus, $cardType)
    {
        $anonPayment = new AnonPayment;
        $anonPayment->isDeleted = 0;
        $anonPayment->version = 0;
        $anonPayment->cardType = isset(Payment::CardType[$cardType]) ? Payment::CardType[$cardType] : $cardType;
        $anonPayment->paymentStatus = $paymentStatus;
        $anonPayment->stripeChargeId = $charge->id;
        $anonPayment->stripeChargeJson = json_encode($charge);
        $anonPayment->pricing_id = $pricing->id;
        $anonPayment->save();
        $this->updateSequence('backend_mysql', 'anon_payment', 'AnonPayment_SEQ');
        return $anonPayment;
    }
}
