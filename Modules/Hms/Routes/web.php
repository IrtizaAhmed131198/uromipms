<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::middleware('web', 'auth', 'language', 'AdminSidebarMenu')->prefix('hms')->group(function () {
    Route::get('dashboard', [Modules\Hms\Http\Controllers\HmsController::class, 'index']);
    Route::resource('/rooms', Modules\Hms\Http\Controllers\RoomController::class);
    Route::resource('/buildings', Modules\Hms\Http\Controllers\HmsBuildingController::class)->names('hms.buildings');
    Route::resource('/floors', Modules\Hms\Http\Controllers\HmsFloorController::class)->names('hms.floors');
    Route::get('/get-floors-by-building', [Modules\Hms\Http\Controllers\HmsFloorController::class, 'getByBuilding'])->name('hms.floors.by_building');
    Route::get('/room_status', [Modules\Hms\Http\Controllers\RoomController::class,'room_status']);
    Route::get('/room/{id}/destroy', [Modules\Hms\Http\Controllers\RoomController::class, 'destroy']);
    Route::get('/room/delete-media/{media_id}', [Modules\Hms\Http\Controllers\RoomController::class, 'deleteMedia'])->name('hms.room.delete_media');
    Route::get('/room/pricing', [Modules\Hms\Http\Controllers\RoomController::class, 'pricing'])->name('room_pricing');
    Route::get('/room/spacial-pricing', [Modules\Hms\Http\Controllers\RoomController::class, 'get_spacial_pricing_html'])->name('get_spacial_pricing_html');
    Route::post('/room/pricing', [Modules\Hms\Http\Controllers\RoomController::class, 'post_pricing']);

    // hms extra  route
    Route::resource('/extras', Modules\Hms\Http\Controllers\ExtraController::class);
    Route::get('/extra/{id}/destroy', [Modules\Hms\Http\Controllers\ExtraController::class, 'destroy'])->name('delete_extra');

    Route::get('settings', [Modules\Hms\Http\Controllers\HmsSettingController::class, 'index']);
    Route::post('settings', [Modules\Hms\Http\Controllers\HmsSettingController::class, 'store']);
    Route::post('settings-print-pdf', [Modules\Hms\Http\Controllers\HmsSettingController::class, 'post_pdf']);


    Route::post('store-email-template', [Modules\Hms\Http\Controllers\HmsSettingController::class, 'store_email_template']);
    
    // booking route — specific routes must come BEFORE the resource wildcard routes
    Route::get('/bookings/available-rooms-for-change', [Modules\Hms\Http\Controllers\HmsBookingController::class, 'get_available_rooms_for_change'])->name('hms.bookings.available_rooms_for_change');
    Route::resource('/bookings', Modules\Hms\Http\Controllers\HmsBookingController::class)->names('hms.bookings');
    Route::post('/bookings/{id}/cancel', [Modules\Hms\Http\Controllers\HmsBookingController::class, 'cancel'])->name('hms.bookings.cancel');
    Route::get('/bookings/{id}/change-room', [Modules\Hms\Http\Controllers\HmsBookingController::class, 'get_change_room'])->name('hms.bookings.change_room');
    Route::post('/bookings/{id}/change-room', [Modules\Hms\Http\Controllers\HmsBookingController::class, 'post_change_room'])->name('hms.bookings.post_change_room');
    Route::get('/booking/{id}/receipt', [
        \Modules\Hms\Http\Controllers\HmsBookingController::class,
        'generateReceipt'
    ])->name('hms.booking.receipt');
    Route::get('/booking-room-add', [Modules\Hms\Http\Controllers\HmsBookingController::class, 'booking_room_add'])->name('booking_room_add');

    Route::get('/booking-room-edit', [Modules\Hms\Http\Controllers\HmsBookingController::class, 'booking_room_edit'])->name('booking_room_edit');

    Route::get('/get-room-type-by', [Modules\Hms\Http\Controllers\HmsBookingController::class, 'get_room_type_by'])->name('get_room_type_by');

    Route::get('/get-room-detail', [Modules\Hms\Http\Controllers\HmsBookingController::class, 'get_room_detail'])->name('get_room_detail');
    Route::get('/print/{id}', [Modules\Hms\Http\Controllers\HmsBookingController::class, 'print'])->name('print');

    // coupons code
    Route::resource('/coupons', Modules\Hms\Http\Controllers\HmsCouponController::class);
    Route::get('/coupon/{id}/destroy', [Modules\Hms\Http\Controllers\HmsCouponController::class, 'destroy'])->name('delete_coupon');

    Route::get('/get-coupon-discount', [Modules\Hms\Http\Controllers\HmsCouponController::class, 'get_coupon_discount'])->name('get_coupon_discount');

    Route::get('/calendar', [Modules\Hms\Http\Controllers\HmsBookingController::class, 'calendar'])->name('booking_calendar');
    Route::get('/get-check-in-out/{id}', [Modules\Hms\Http\Controllers\HmsBookingController::class, 'get_check_in_out'])->name('get_check_in_out');
    Route::put('/get-check-in-out/{id}', [Modules\Hms\Http\Controllers\HmsBookingController::class, 'post_check_in_out'])->name('post_check_in_out');

    Route::resource('/unavailables', Modules\Hms\Http\Controllers\UnavailableController::class);

    Route::get('/unavailable/{id}/destroy', [Modules\Hms\Http\Controllers\UnavailableController::class, 'destroy'])->name('delete_unavailable');

    // General guest report routes MUST be before Route::resource('/reports') to prevent
    // the resource route from capturing /reports/general-guest as /reports/{id}
    Route::get('/reports/general-guest', [\Modules\Hms\Http\Controllers\HmsReportController::class, 'general_guest_report'])->name('hms.reports.general_guest');
    Route::get('/reports/general-guest/data', [\Modules\Hms\Http\Controllers\HmsReportController::class, 'general_guest_data'])->name('hms.reports.general_guest.data');
    Route::get('/reports/general-guest/pdf', [\Modules\Hms\Http\Controllers\HmsReportController::class, 'general_guest_pdf'])->name('hms.reports.general_guest.pdf');
    Route::get('/reports/general-guest/excel', [\Modules\Hms\Http\Controllers\HmsReportController::class, 'general_guest_excel'])->name('hms.reports.general_guest.excel');

    Route::resource('/reports', Modules\Hms\Http\Controllers\HmsReportController::class);

    Route::get('install', [\Modules\Hms\Http\Controllers\InstallController::class, 'index']);
    Route::post('install', [\Modules\Hms\Http\Controllers\InstallController::class, 'install']);
    Route::get('install/uninstall', [\Modules\Hms\Http\Controllers\InstallController::class, 'uninstall']);
    Route::get('install/update', [\Modules\Hms\Http\Controllers\InstallController::class, 'update']);

    Route::post('/booking/{id}/cancel', [\Modules\Hms\Http\Controllers\HmsBookingController::class, 'cancel'])->name('hms.booking.cancel');
    Route::delete('/booking/{id}', [\Modules\Hms\Http\Controllers\HmsBookingController::class, 'destroy'])->name('hms.booking.destroy');

    Route::get('/get-change-room/{id}', [\Modules\Hms\Http\Controllers\HmsBookingController::class, 'get_change_room'])->name('hms.booking.get_change_room');
    Route::put('/post-change-room/{id}', [\Modules\Hms\Http\Controllers\HmsBookingController::class, 'post_change_room'])->name('hms.booking.post_change_room');
    Route::get('/get-change-room-type-by', [\Modules\Hms\Http\Controllers\HmsBookingController::class, 'get_change_room_type_by'])->name('get_change_room_type_by');
    Route::get('/get-change-room-price', [\Modules\Hms\Http\Controllers\HmsBookingController::class, 'get_change_room_price'])->name('get_change_room_price');

    Route::get('/get-extend-stay/{id}', [\Modules\Hms\Http\Controllers\HmsBookingController::class, 'get_extend_stay'])->name('hms.booking.get_extend_stay');
    Route::post('/post-extend-stay/{id}', [\Modules\Hms\Http\Controllers\HmsBookingController::class, 'post_extend_stay'])->name('hms.booking.post_extend_stay');

});