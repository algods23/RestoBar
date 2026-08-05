<?php

namespace App\Support;

use Mike42\Escpos\PrintConnectors\PrintConnector;

class WindowsSerialPrintConnector implements PrintConnector
{
    private string $buffer = '';
    private bool $finalized = false;

    public function __construct(private readonly string $port)
    {
        if (! preg_match('/^COM\d+$/i', $port)) {
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

        $port = strtoupper($this->port);
        $file = tempnam(sys_get_temp_dir(), 'restobar-print-');
        if ($file === false) {
            throw new \RuntimeException('Could not create temporary print file.');
        }

        file_put_contents($file, $this->buffer);

        try {
            exec("mode {$port}: BAUD=9600 PARITY=N DATA=8 STOP=1", $modeOutput, $modeCode);
            exec('cmd /C copy /B ' . escapeshellarg($file) . ' ' . $port, $copyOutput, $copyCode);

            if ($copyCode !== 0) {
                throw new \RuntimeException('Could not send print data to ' . $port . '. Check that the Bluetooth printer is paired, turned on, and not connected to another app.');
            }
        } finally {
            @unlink($file);
        }
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
