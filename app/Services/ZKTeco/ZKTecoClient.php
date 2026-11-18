<?php

namespace App\Services\ZKTeco;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class ZKTecoClient
{
    private const CMD_CONNECT       = 1000;
    private const CMD_EXIT          = 1001;
    private const CMD_ENABLEDEVICE  = 1002;
    private const CMD_DISABLEDEVICE = 1003;
    private const CMD_ATTLOG_RRQ    = 13;
    private const CMD_PREPARE_DATA  = 1500;
    private const CMD_DATA          = 1501;
    private const CMD_FREE_DATA     = 1502;
    private const CMD_ACK_OK        = 2000;
    private const CMD_ACK_ERROR     = 2001;
    private const CMD_ACK_UNAUTH    = 2005;

    private const MAX_PACKET_BYTES = 20_000_000;

    /** @var resource|null */
    private $socket = null;

    private int $sessionId = 0;
    private int $replyId   = 0;

    public function __construct(
        private string $ip,
        private int $port = 4370,
        private int $commKey = 0,
        private int $timeout = 30
    ) {}

    public function connect(): void
    {
        $this->socket = @fsockopen(
            $this->ip,
            $this->port,
            $errno,
            $errstr,
            $this->timeout
        );

        if (!$this->socket) {
            throw new RuntimeException(
                "Cannot connect to {$this->ip}:{$this->port} — {$errstr} ({$errno})"
            );
        }

        stream_set_timeout($this->socket, $this->timeout);

        $reply = $this->send(self::CMD_CONNECT, '');
        $ack   = $this->unpackShort($reply, 0);

        if ($ack === self::CMD_ACK_UNAUTH) {
            if ($this->commKey === 0) {
                throw new RuntimeException(
                    'Device requires comm-key. Set it to 0 on the device (Menu → Comm → Security), then reboot.'
                );
            }

            $tick = time();
            $k    = $this->makeCommKey($this->commKey, $tick);
            $payload = pack('V', $tick) . pack('V', $k);
            $reply = $this->send(self::CMD_CONNECT, $payload);
            $ack   = $this->unpackShort($reply, 0);

            if ($ack !== self::CMD_ACK_OK) {
                throw new RuntimeException('comm-key auth failed (ack=' . $ack . ')');
            }
        } elseif ($ack !== self::CMD_ACK_OK) {
            throw new RuntimeException('Unexpected connect reply (ack=' . $ack . ')');
        }
    }

    public function disableDevice(): void { $this->send(self::CMD_DISABLEDEVICE, ''); }
    public function enableDevice():  void { $this->send(self::CMD_ENABLEDEVICE, ''); }

    public function disconnect(): void
    {
        if ($this->socket) {
            try { $this->send(self::CMD_EXIT, ''); } catch (\Throwable) {}
            @fclose($this->socket);
            $this->socket = null;
        }
    }

    public function getAttendance(): array
    {
        $reply = $this->send(self::CMD_ATTLOG_RRQ, '');
        $ack   = $this->unpackShort($reply, 0);

        if ($ack === self::CMD_ACK_ERROR) {
            throw new RuntimeException('Device refused ATTLOG request');
        }

        if (strlen($reply) < 8) {
            return [];
        }

        // PREPARE_DATA reply: [4-byte buffer size][4-byte cmd 1500][4-byte count]
        $bufferSize = $this->unpackInt($reply, 0);

        if ($bufferSize < 8 || $bufferSize > self::MAX_PACKET_BYTES) {
            // Some firmware puts cmd first
            $bufferSize = $this->unpackInt($reply, 4);
        }

        if ($bufferSize < 8 || $bufferSize > self::MAX_PACKET_BYTES) {
            Log::warning('ZKTeco ATTLOG: no valid PREPARE_DATA header', [
                'reply_hex' => bin2hex(substr($reply, 0, 32)),
                'reply_len' => strlen($reply),
            ]);
            return [];
        }

        $data = $this->readBytes($bufferSize);

        try { $this->send(self::CMD_FREE_DATA, ''); } catch (\Throwable) {}

        return $this->decodeAttendanceData($data);
    }

    /* ============================================================ */

    private function makeCommKey(int $key, int $ticks): int
    {
        $k = 0;
        $key &= 0xFFFFFFFF;

        for ($i = 0; $i < 4; $i++) {
            $k = (($k + $key) & 0xFFFFFFFF) ^ 0x4B42D3B3;
        }
        $k = (($k + ($ticks & 0xFFFFFFFF)) & 0xFFFFFFFF) ^ 0x4B42D3B3;

        return $k & 0xFFFFFFFF;
    }

    /**
     * Send a command and read the reply.
     *
     * Packet layout (correct):
     *   [4-byte total length] [2 cmd] [2 checksum] [2 session] [2 reply] [payload...]
     *                          ↑—— 8-byte header ——↑
     */
    private function send(int $command, string $payload = ''): string
    {
        $this->replyId = ($this->replyId + 1) & 0xFFFF;

        $pad = (8 - (strlen($payload) % 8)) % 8;
        if ($pad > 0) {
            $payload .= str_repeat("\x00", $pad);
        }

        // ⭐ Correct: 4 × u16 = 8-byte header
        $header = pack(
            'vvvv',
            $command & 0xFFFF,
            0,
            $this->sessionId & 0xFFFF,
            $this->replyId
        );

        $body = $header . $payload;
        $totalLength = strlen($body) + 4;

        $packet = pack('V', $totalLength) . $body;

        $written = @fwrite($this->socket, $packet);
        if ($written === false || $written < strlen($packet)) {
            throw new RuntimeException('Failed to write packet');
        }

        return $this->readPacket();
    }

    private function readPacket(): string
    {
        $lenBytes = $this->readBytes(4);
        $length   = unpack('V', $lenBytes)[1] ?? 0;

        if ($length < 8 || $length > self::MAX_PACKET_BYTES) {
            throw new RuntimeException("Invalid packet length: {$length}");
        }

        $body = $this->readBytes($length - 4);

        $ack       = unpack('v', substr($body, 0, 2))[1] ?? 0;
        $sessionId = unpack('v', substr($body, 4, 2))[1] ?? $this->sessionId;

        $this->sessionId = $sessionId;

        if ($ack === self::CMD_ACK_UNAUTH) {
            throw new RuntimeException('Device rejected authentication');
        }

        return $body;
    }

    private function readBytes(int $n): string
    {
        $buf = '';
        while (strlen($buf) < $n) {
            $chunk = @fread($this->socket, $n - strlen($buf));
            if ($chunk === false || $chunk === '') {
                $info = stream_get_meta_data($this->socket);
                if (!empty($info['timed_out'])) {
                    throw new RuntimeException('Device timed out while responding');
                }
                throw new RuntimeException('Socket closed while reading data');
            }
            $buf .= $chunk;
        }
        return $buf;
    }

    private function unpackShort(string $buf, int $offset): int
    {
        return unpack('v', substr($buf, $offset, 2))[1] ?? 0;
    }

    private function unpackInt(string $buf, int $offset): int
    {
        return unpack('V', substr($buf, $offset, 4))[1] ?? 0;
    }

    private function decodeAttendanceData(string $data): array
    {
        $records = [];

        foreach (explode("\x00", $data) as $line) {
            $line = trim($line);
            if ($line === '' || !str_contains($line, "\t")) continue;

            $parts = explode("\t", $line);
            if (count($parts) < 2) continue;

            $records[] = [
                'id'        => trim($parts[0]),
                'timestamp' => trim($parts[1]),
                'state'     => isset($parts[2]) ? (int) $parts[2] : 0,
                'type'      => isset($parts[3]) ? (int) $parts[3] : 1,
                'work_code' => isset($parts[4]) && $parts[4] !== '' ? trim($parts[4]) : null,
            ];
        }

        return $records;
    }
}
