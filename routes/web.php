<?php

use App\Http\Controllers\Auth\SocialAuthenticationController;
use App\Http\Controllers\Test\TestController;
use App\Livewire\ReportsOld;
use App\Livewire\Calendar;
use App\Livewire\Reports;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



Route::prefix(env('BASE_URL'))->group(function () {
    Route::get('/', function () {
        return "welcome to parknjet.";
        // return redirect()->away(env('APP_URL'));
    })->name('index');
    
    Route::controller(TestController::class)->group(function () {
        Route::get('/test_mail', 'test_mail')->name('test_mail');
        Route::get('/test_apple_pay', 'test_apple_pay')->name('test_apple_pay');
        Route::get('/test/guest/reservation/{rsvnid}', 'guest_reservation')->name('test.guest.reservation');
        Route::get('/test/member/reservation/{rsvnid}', 'member_reservation')->name('test.member.reservation');
        Route::get('/test/member/reservation/sync/{rsvnid}', 'member_reservation_sync')->name('test.member.reservation_sync');
        Route::get('/test/stripe', 'cardTokenGenerate')->name('cardTokenGenerate');
        Route::get('/test/findSequence', 'findSequence')->name('findSequence');
        Route::get('/test/retrievePaymentIntents/{lot}/{pid}', 'retrievePaymentIntents')->name('retrievePaymentIntents');
        Route::get('/test/test_reseravation/{rsvnid}', 'test_reseravation')->name('test_reseravation');
        Route::get('/test/test_fix_payment_sync/{rsvnid}', 'test_fix_payment_sync')->name('test_fix_payment_sync');
    });

    require __DIR__ . '/auth.php';

    Route::controller(SocialAuthenticationController::class)->group(function () {
        Route::get('/login/apple', 'login_apple')->name('oauth.apple');
        Route::post('/callback/apple', 'callback_apple')->name('callback.apple');
        Route::get('/redirectTo/{client}', 'redirectTo')->name('oauth.redirect');
    });

    Route::get('/admin/pricing', Calendar::class)->name('calendar');
    Route::get('/admin/reports_old', ReportsOld::class)->name('reports_old');
    Route::get('/admin/reports', Reports::class)->name('reports');
});
