<?php

$ip = '192.168.1.100';
$ports = [4370, 80, 443, 23, 22, 8080];

echo "Scanning {$ip}...\n\n";

foreach ($ports as $port) {
    $conn = @fsockopen($ip, $port, $errno, $errstr, 2);

    if ($conn) {
        echo "✅ TCP {$port} — OPEN\n";
        fclose($conn);
    } else {
        echo "❌ TCP {$port} — CLOSED ({$errstr})\n";
    }
}
