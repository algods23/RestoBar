<?php

namespace App\Support;

use Mike42\Escpos\PrintConnectors\PrintConnector;

class WindowsSerialPrintConnector implements PrintConnector
{
    private string $buffer = '';
    private bool $finalized = false;

    public function __construct(private readonly string $port)
    {
        $normalized = strtoupper(trim($port));
        if (! preg_match('/^COM\d+$/i', $normalized)) {
            throw new \InvalidArgumentException('Invalid Bluetooth COM port: ' . $port);
        }
    }

    public function __destruct()
    {
        if (! $this->finalized) {
            trigger_error('WindowsSerialPrintConnector was not finalized', E_USER_NOTICE);
        }
    }

    public function finalize()
    {
        $this->finalized = true;

        if ($this->buffer === '') {
            return;
        }

        $port = strtoupper(trim($this->port));
        $file = tempnam(sys_get_temp_dir(), 'restobar-print-');
        if ($file === false) {
            throw new \RuntimeException('Could not create temporary print file.');
        }

        file_put_contents($file, $this->buffer);

        try {
            // Configure mode with a 3-second hard timeout
            $modeCmd = "mode {$port}: BAUD=9600 PARITY=N DATA=8 STOP=1";
            self::runWithTimeout($modeCmd, 3);

            // Copy file to COM port with a 4-second hard timeout
            $copyCmd = 'cmd /C copy /B ' . escapeshellarg($file) . ' ' . $port;
            $copyRes = self::runWithTimeout($copyCmd, 4);

            if ($copyRes['code'] !== 0) {
                $errDetail = $copyRes['error'] ?: $copyRes['output'];
                throw new \RuntimeException('Could not send print data to ' . $port . ' (' . ($errDetail ?: 'Printer not responding') . '). Check that the Bluetooth printer is paired, turned on, and within range.');
            }
        } finally {
            @unlink($file);
        }
    }

    /**
     * Run a command with a strict execution timeout using proc_open to prevent server freezes.
     */
    public static function runWithTimeout(string $cmd, int $timeoutSeconds = 3): array
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptors, $pipes);
        if (! is_resource($process)) {
            return ['code' => -1, 'output' => '', 'error' => 'Failed to start process'];
        }

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        fclose($pipes[0]);

        $startTime = microtime(true);
        $stdout = '';
        $stderr = '';

        while (true) {
            $stdout .= stream_get_contents($pipes[1]);
            $stderr .= stream_get_contents($pipes[2]);

            $status = proc_get_status($process);
            if (! $status['running']) {
                $exitCode = $status['exitcode'];
                break;
            }

            if ((microtime(true) - $startTime) >= $timeoutSeconds) {
                proc_terminate($process, 9);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                return ['code' => -1, 'output' => $stdout, 'error' => "Operation on port timed out after {$timeoutSeconds} seconds."];
            }

            usleep(20000); // 20ms check interval
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return ['code' => $exitCode, 'output' => trim($stdout), 'error' => trim($stderr)];
    }

    public function read($len)
    {
        return false;
    }

    public function write($data)
    {
        $this->buffer .= $data;
    }
}

