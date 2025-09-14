<?php

define('HASH_ALGORITHM', 'SHA256', );
define('HASH_ITERATIONS', 1024, );
define('HASH_LENGTH', 80, );

define('vehicle_length', [
    'STANDARD' => 'Under 17ft',
    'LARGE' => '17-19ft',
    'EXTRA_LARGE' => '19-21ft',
    'image' => [
        'STANDARD' => 'car.png',
        'LARGE' => 'car_l.png',
        'EXTRA_LARGE' => 'car_xl.png',
    ]
]);

define('LotType', [
    'LOT_1' => 'LOT_1',
    'LOT_2' => 'LOT_2',
]);

define('TriggerType', [
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
]);

define('people', [
    '14258225000' => 'Paul Basra',
    '12062359000' => 'Jag Paul'
]);

define('port_phones', [
    "+1800599" => "Port of Seattle",
    "+1800600" => "Port of Seattle",
    "+12067872244" => "Port of Seattle",
    "+12064390060" => "Port of Seattle"
]);

define('WalletType', [
    "EARNED" => "EARNED",
    "PREPAID" => "PREPAID",
]);

define('ReservationStatus', [
    'NEW' => 'NEW',
    'CANCELLED' => 'CANCELLED',
    'EXTENDED' => 'EXTENDED',
    'SHORTENED' => 'SHORTENED',
    'NO_SHOW' => 'NO_SHOW',
    'TEMP' => 'TEMP',
    'CHECKED_IN' => 'CHECKED_IN',
    'CHECKED_OUT' => 'CHECKED_OUT',
]);

define('Wallet', [
    "REFERRAL_BONUS_DAYS" => 1
]);

define('TransactionType', [
    'CREDIT' => 'CREDIT',
    'DEBIT' => 'DEBIT',
]);