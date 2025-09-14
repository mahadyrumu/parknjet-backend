<?php

class Payment
{
    const PaymentType = [
        'ONLINE' => "ONLINE",
        'NOT_ONLINE' => "NOT_ONLINE",
    ];

    const TransactionType = [
        'CREDIT' => 'CREDIT',
        'DEBIT' => 'DEBIT',
    ];

    const PaymentStatus = [
        'PENDING' => 'PENDING',
        'PAID' => 'PAID',
        'REFUNDED' => 'REFUNDED',
        'DISPUTED' => 'DISPUTED',
        'PAID_AT_LOT' => 'PAID_AT_LOT',
    ];

    const CardType = [
        'visa' => 'VISA',
        'Visa' => 'VISA',
        'mastercard' => 'MC',
        'MasterCard' => 'MC',
        'MASTERCARD' => 'MC',
        'amex' => 'AMEX',
        'Amex' => 'AMEX',
        'american express' => 'AMEX',
        'American Express' => 'AMEX',
        'Discover' => 'DISCOVER',
        'discover' => 'DISCOVER',
        'Diners Club' => 'DINERS CLUB',
    ];

    const TriggerType = [
        'PREPAID_PACKAGE' => 'PREPAID_PACKAGE',
        'RESERVATION_CANCELLATION' => 'RESERVATION_CANCELLATION',
        'REFERRAL' => 'REFERRAL',
        'RESERVATION_PAID' => 'RESERVATION_PAID',
        'MAX_POINTS_REACHED' => 'MAX_POINTS_REACHED',
        'RESERVATION_CHECKED_OUT' => 'RESERVATION_CHECKED_OUT',
        'EARLY_PICKUP' => 'EARLY_PICKUP',
        'COMPLIMENTARY' => 'COMPLIMENTARY',
        'REFUND' => 'REFUND',
        'MISCELLENIOUS' => 'MISCELLENIOUS',
        'SIGNUP_COUPON' => 'SIGNUP_COUPON',
    ];

}
