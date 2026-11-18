<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ZKTecoPushController;
use Illuminate\Support\Facades\DB;

Route::prefix('iclock')->group(function () {
    Route::get('/cdata', [ZKTecoPushController::class, 'handshake']);
    Route::post('/cdata', [ZKTecoPushController::class, 'cdata']);
    Route::get('/getrequest', [ZKTecoPushController::class, 'getRequest']);
    Route::post('/devicecmd', [ZKTecoPushController::class, 'deviceCmd']);
});

Route::get('/debug/device', function () {
    $d = \App\Models\Device::find(1);
    return response()->json([
        'db'            => DB::getDatabaseName(),
        'device'        => $d ? $d->only(['id', 'serial_number', 'last_seen_at', 'last_sync_at']) : null,
        'now'           => now()->toDateTimeString(),
        'server_time'   => date('Y-m-d H:i:s'),
        'php_timezone'  => config('app.timezone'),
        'db_timezone'   => DB::selectOne('SELECT @@session.time_zone AS tz')->tz,
    ]);
});
