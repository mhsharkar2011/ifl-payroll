<?php

$ip = '192.168.1.100';
$port = 4370;
$password = 1324;

echo "=== ZKTeco Raw UDP Test ===\n\n";

// 1. Check extension
if (!extension_loaded('sockets')) {
    die("❌ Sockets extension NOT loaded\n");
}
echo "✅ Sockets extension loaded\n";

// 2. Create socket
$socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!$socket) {
    die("❌ Socket create failed: " . socket_strerror(socket_last_error()) . "\n");
}
echo "✅ Socket created\n";

// 3. Bind (Windows fix)
if (!@socket_bind($socket, '0.0.0.0', 0)) {
    echo "⚠️ Bind warning: " . socket_strerror(socket_last_error($socket)) . "\n";
} else {
    echo "✅ Socket bound\n";
}

// 4. Set timeout
$tv = ['sec' => 5, 'usec' => 0];
socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $tv);
socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $tv);

// 5. Build CMD_CONNECT packet
// Header: command(2) + checksum(2) + session(2) + reply(2)
$packet = pack('vvvv', 1000, 0, 0, 0);

// Add password (uint32)
$packet .= pack('V', $password);

echo "📤 Sending " . strlen($packet) . " bytes to {$ip}:{$port}...\n";
echo "📦 Hex: " . bin2hex($packet) . "\n\n";

// 6. Send
$sent = @socket_sendto($socket, $packet, strlen($packet), 0, $ip, $port);
if ($sent === false) {
    die("❌ Send failed: " . socket_strerror(socket_last_error($socket)) . "\n");
}
echo "✅ Sent {$sent} bytes\n";

// 7. Receive
$buffer = '';
$from = '';
$fromPort = 0;
$recv = @socket_recvfrom($socket, $buffer, 1024, 0, $from, $fromPort);

if ($recv === false) {
    echo "\n❌ RECV FAILED: " . socket_strerror(socket_last_error($socket)) . "\n";
    echo "\n⚠️ TROUBLESHOOTING:\n";
    echo "1. Ping device:  ping {$ip}\n";
    echo "2. Firewall:     Allow UDP {$port}\n";
    echo "3. Password:     Check device Comm Key\n";
    echo "4. Device on?    Check power light\n";
    echo "5. Network:      Same subnet?\n";
} elseif ($recv === 0) {
    echo "\n⚠️ Received 0 bytes (empty reply)\n";
} else {
    echo "\n✅ Received {$recv} bytes from {$from}:{$fromPort}\n";
    echo "📦 Hex: " . bin2hex($buffer) . "\n";

    if (strlen($buffer) >= 8) {
        $header = unpack('vcmd/vchk/vsession/vreply', substr($buffer, 0, 8));
        echo "🎉 SUCCESS! Device responded:\n";
        echo "   Command: {$header['cmd']}\n";
        echo "   Session: {$header['session']}\n";
        echo "   Reply:   {$header['reply']}\n";
    }
}

@socket_close($socket);
echo "\n=== Done ===\n";
