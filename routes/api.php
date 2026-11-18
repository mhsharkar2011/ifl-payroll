<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ZKTecoPushController;

/*
|--------------------------------------------------------------------------
| ZKTeco Push SDK Routes
|--------------------------------------------------------------------------
| These routes are called BY the ZKTeco device.
| No auth required — device identifies itself by SN (serial number).
|
| ⚠️ IMPORTANT: These routes are OUTSIDE the 'auth' middleware.
*/

Route::prefix('iclock')->group(function () {
    // Device handshake / initial connect
    Route::get('/cdata', [ZKTecoPushController::class, 'handshake']);

    // Device sends data (attendance, users, operations)
    Route::post('/cdata', [ZKTecoPushController::class, 'cdata']);

    // Device polls for pending commands
    Route::get('/getrequest', [ZKTecoPushController::class, 'getRequest']);

    // Device acknowledges command completion
    Route::post('/devicecmd', [ZKTecoPushController::class, 'deviceCmd']);
});

// Keep default user route
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); #}
