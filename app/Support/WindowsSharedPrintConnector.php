<?php

namespace App\Support;

use Mike42\Escpos\PrintConnectors\PrintConnector;
use InvalidArgumentException;
use RuntimeException;

class WindowsSharedPrintConnector implements PrintConnector
{
    private string $buffer = '';
    private bool $finalized = false;

    public function __construct(private readonly string $printerName)
    {
        if (trim($printerName) === '') {
            throw new InvalidArgumentException('Printer name cannot be empty.');
        }
    }

    public function __destruct()
    {
        if (! $this->finalized) {
            trigger_error('WindowsSharedPrintConnector was not finalized', E_USER_NOTICE);
        }
    }

    public function finalize()
    {
        $this->finalized = true;

        if ($this->buffer === '') {
            return;
        }

        $file = tempnam(sys_get_temp_dir(), 'restobar-print-');
        if ($file === false) {
            throw new RuntimeException('Could not create temporary print file.');
        }

        file_put_contents($file, $this->buffer);

        try {
            // Copy file to the shared printer or LPT port with a 4-second hard timeout
            $cmd = 'cmd /C copy /B ' . escapeshellarg($file) . ' ' . escapeshellarg($this->printerName);
            $res = WindowsSerialPrintConnector::runWithTimeout($cmd, 4);

            if ($res['code'] !== 0) {
                $errDetail = $res['error'] ?: $res['output'];
                throw new RuntimeException('Could not send print data to ' . $this->printerName . ' (' . ($errDetail ?: 'Printer not responding') . ').');
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
