<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public URL for ZKTeco Push SDK
    |--------------------------------------------------------------------------
    |
    | This is the URL your ZKTeco device should use to reach this server.
    | It is shown in the admin Devices page so admins know exactly what
    | to enter in the device's "Server Address" field.
    |
    | Format: scheme://host:port  (NO trailing slash, NO /iclock suffix)
    |
    | Examples:
    |   LAN only:        http://192.168.1.9:8080
    |   Public IP:       http://210.4.77.83:8080
    |   Cloudflare:      https://random-words.trycloudflare.com
    |   ngrok:           https://a1b2-103-94-12-34.ngrok-free.app
    |
    | If the device is on the same LAN as the server, use the LAN IP.
    | If the device is remote, use a public IP or tunnel URL.
    |
    */

    'public_url' => env('ICLOCK_PUBLIC_URL', 'http://localhost:8080'),

    /*
    |--------------------------------------------------------------------------
    | Sync configuration
    |--------------------------------------------------------------------------
    |
    | These control the behavior of the Push SDK handshake and how
    | many commands are handed to the device per heartbeat.
    |
    */

    // How many pending commands to hand out per heartbeat poll.
    'commands_per_poll' => env('ICLOCK_COMMANDS_PER_POLL', 10),

    // Heartbeat interval (in seconds) the device should use.
    'heartbeat_interval' => env('ICLOCK_HEARTBEAT_INTERVAL', 10),

    // Timezone offset for the device. Bangladesh = +6.
    'timezone_offset' => env('ICLOCK_TIMEZONE_OFFSET', 6),

    // Max size of an attendance push body (in bytes). Prevents runaway devices.
    'max_body_bytes' => env('ICLOCK_MAX_BODY_BYTES', 5 * 1024 * 1024), // 5 MB

    /*
    |--------------------------------------------------------------------------
    | Recent-device window (seconds)
    |--------------------------------------------------------------------------
    |
    | A device is considered "Online" if it has sent a heartbeat within
    | this many seconds. The Devices page shows Online / Recent / Offline
    | based on this value.
    |
    */

    'online_threshold_seconds' => env('ICLOCK_ONLINE_THRESHOLD', 120),  // 2 minutes
    'recent_threshold_seconds' => env('ICLOCK_RECENT_THRESHOLD', 600), // 10 minutes

];
