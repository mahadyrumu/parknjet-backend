<?php

use App\Http\Controllers\PNJ\API\Admin\AdminReportController;
use App\Http\Controllers\PNJ\API\Admin\AdminUserController;
use App\Http\Controllers\PNJ\API\Auth\AuthenticationController;
use App\Http\Controllers\PNJ\API\Driver\DriverController;
use App\Http\Controllers\PNJ\API\Payment\StripeController;
use App\Http\Controllers\PNJ\API\Point\PointController;
use App\Http\Controllers\PNJ\API\PrepaidPackage\PrepaidPackageController;
use App\Http\Controllers\PNJ\API\Profile\ProfileController;
use App\Http\Controllers\PNJ\API\Quote\QuoteController;
use App\Http\Controllers\PNJ\API\Sync\LotStatusSyncController;
use App\Http\Controllers\PNJ\API\Sync\ResyncController;
use App\Http\Controllers\PNJ\API\Referral\ReferralController;
use App\Http\Controllers\PNJ\API\Reservation\ReservationController;
use App\Http\Controllers\PNJ\API\Vehicle\VehicleController;
use App\Http\Controllers\PNJ\Dispatch\DispatchTwilioCall;
use App\Http\Controllers\PNJ\Dispatch\DispatchTwilioSms;
use App\Http\Controllers\PNJ\API\PickupRequest\PickupRequestController;
use App\Http\Controllers\PNJ\API\ReservationFinder\ReservationFinderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix(env('API_BASE_URL'))->group(function () {

    Route::controller(AuthenticationController::class)->group(function () {
        Route::post('user/token', 'getToken');
        Route::post('user/signin', 'signIn');
        Route::post('user/signin/apple', 'appleSignIn');
        Route::post('user/signup', 'register');
        Route::post('user/forgot_password', 'forgotPassword');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::controller(AuthenticationController::class)->group(function () {
            Route::post('user/signout', 'signOut');
            Route::post('token/refresh', 'refreshToken');
        });

        Route::middleware('auth.check')->group(function () {
            Route::controller(ProfileController::class)->group(function () {
                Route::get('users/{id}', 'getUser');
                Route::put('users/{id}', 'updateUser');
                Route::put('users/{id}/password', 'updatePassword');
            });

            Route::controller(VehicleController::class)->group(function () {
                Route::get('vehicles/{owner_id}', 'getVehicles');
                Route::get('vehicles/{owner_id}/{id}', 'getVehicle');
                Route::post('vehicles/{owner_id}', 'createVehicle');
                Route::put('vehicles/{owner_id}/{id}', 'updateVehicle');
                Route::delete('vehicles/{owner_id}/{id}', 'destroy');
            });

            Route::controller(DriverController::class)->group(function () {
                Route::get('drivers/{owner_id}', 'getDrivers');
                Route::get('drivers/{owner_id}/{id}', 'getDriver');
                Route::post('drivers/{owner_id}', 'createDriver');
                Route::put('drivers/{owner_id}/{id}', 'updateDriver');
                Route::delete('drivers/{owner_id}/{id}', 'destroy');
            });

            Route::controller(PickupRequestController::class)->group(function () {
                Route::get('pickup_request/{owner_id}', 'getPickupRequest');
                Route::post('pickup_request', 'createPickupRequest');
            });

            Route::controller(ReferralController::class)->group(function () {
                Route::get('referrals/{owner_id}/all', 'getAllMemberReferral');
                Route::get('referrals/{owner_id}/{email}', 'getMemberReferral');
                Route::post('referrals/{owner_id}', 'createReferral');
            });

            Route::controller(PointController::class)->group(function () {
                Route::get('prepaid_days/{owner_id}', 'getPrepaidDays');
                Route::get('points/{owner_id}', 'getPointsAndDays');
            });

            Route::controller(ReservationController::class)->group(function () {
                Route::get('reservations/user/{owner_id}', 'getUserReservations');
                Route::get('reservations/{owner_id}/{id}', 'getReservation');
                Route::post('reservations/{owner_id}/details/{id}/send', 'sendReservationDetails');
            });
        });
    });

    Route::middleware(['restrict.ip.port'])->group(function () {
        Route::controller(AdminUserController::class)->group(function () {
            Route::get('users', 'users');
        });
    });

    Route::controller(QuoteController::class)->group(function () {
        Route::get('/quotes/availability', 'checkAvailability')->name('api.check.availability');
        Route::get('/quotes/guest', 'getAnonQuotes');
        Route::get('/quotes/coupon', 'checkCoupon');
        Route::middleware(['auth:sanctum', 'auth.check'])->group(function () {
            Route::get('/quotes/{owner_id}', 'getMemQuotes');
            Route::post('/quotes/{owner_id}/extend', 'extendMemQuotes');
        });
        Route::post('/quotes/extend', 'extendAnonQuotes');
    });

    Route::controller(ReservationController::class)->group(function () {
        Route::middleware(['auth:sanctum', 'auth.check'])->group(function () {
            Route::post('/member/{owner_id}/reservation', 'createMemReservation');
            Route::post('/member/{owner_id}/reservation/{rsvn}/cancel', 'cancelMemReservation');
            Route::post('/member/{owner_id}/reservation/{rsvn}/cancel/refund', 'refundMemCancelReservation');
            Route::post('/member/{owner_id}/reservation/{rsvn}/payment', 'paymentMemReservation');
            Route::post('/member/{owner_id}/reservation/{rsvn}/extend', 'extendMemReservation');
        });
        Route::post('/guest/reservation', 'createAnonReservation');
        Route::post('/guest/reservation/{rsvn}/cancel', 'cancelAnonReservation');
        Route::post('/guest/reservation/{rsvn}/cancel/refund', 'refundAnonCancelReservation');
        //Route::post('/guest/reservation/{rsvn}/refund', 'refundReservation');
        Route::post('/guest/reservation/{rsvn}/payment', 'paymentAnonReservation');
        Route::post('/guest/reservation/{rsvn}/extend', 'extendAnonReservation');
    });

    Route::controller(PrepaidPackageController::class)->group(function () {
        Route::get('/prepaid_packages', 'getPrepaidPackages');
        Route::middleware(['auth:sanctum', 'auth.check'])->group(function () {
            Route::get('/member/{owner_id}/prepaid_package/{lot}/{package_id}', 'getPrepaidPackage');
            Route::post('/member/{owner_id}/prepaid_package/{package_id}', 'buyPackageForAUser');
        });
    });

    Route::controller(PickupRequestController::class)->group(function () {
        Route::post('/pickup_request', 'createPickupRequest');
    });

    Route::controller(ReservationFinderController::class)->group(function () {
        Route::post('/reservation_finder', 'findExistingReservation');
    });

    Route::controller(StripeController::class)->group(function () {
        // Route::post('/stripe_card_payment', 'stripe_card_payment');
        Route::post('/payment_intent', 'create_payment_intent');
    });

    Route::controller(DispatchTwilioSms::class)->group(function () {
        Route::post('twilio/sms', 'twilio_sms')->name('twilio.sms');
    });

    Route::controller(DispatchTwilioCall::class)->group(function () {
        Route::post('twilio/call/greet', 'twilio_greet')->name('twilio.greet');
        Route::post('twilio/call/main_menu', 'twilio_main_menu')->name('twilio.main_menu');
        Route::post('twilio/call/directions/{direction}', 'twilio_directions')->name('twilio.directions');
        Route::post('twilio/call/dispatch_menu/{menu}', 'twilio_dispatch_menu')->name('twilio.dispatch_menu');
        Route::post('twilio/call/confirm_claimid', 'twilio_confirm_claimid')->name('twilio.confirm_claimid');
        Route::post('twilio/call/choose_island', 'twilio_choose_island')->name('twilio.choose_island');
        Route::post('twilio/call/choose_delay', 'twilio_choose_delay')->name('twilio.choose_delay');
    });

    Route::controller(ResyncController::class)->group(function () {
        Route::get('/rsvn/resync', 'resync');
        Route::get('/rsvn/emailchanges', 'sendChangeEmail');
    });

    Route::controller(LotStatusSyncController::class)->group(function () {
        Route::get('/rsvn/sync/{rsvnId}/status', 'updateReservationStatus')->name('rsvn.sync.status');
        Route::get('/rsvn/sync/{rsvnId}/payment', 'updateReservationPayment')->name('rsvn.sync.payment');
    });

    Route::controller(AdminReportController::class)->group(function () {
        Route::get('/report', 'generalReports')->name('report.general');
        // Route::get('/report/reservation', 'reservationReports')->name('report.reservation');
        // Route::get('/report/revenue', 'revenueReports')->name('report.revenue');
    });
});
