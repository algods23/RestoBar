<?php

namespace App\Support;

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\PrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class PrinterConnectorFactory
{
    public static function make(string $printer, string $transport = 'auto'): PrintConnector
    {
        $printer = trim($printer);
        $transport = trim($transport) ?: 'auto';

        if ($printer === '' || self::isPlaceholder($printer)) {
            throw new \InvalidArgumentException('No printer is configured. Open Settings and save a printer first.');
        }

        if ($transport === 'bluetooth' && ! self::isSerialPort($printer) && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            throw new \InvalidArgumentException('Bluetooth printers must use a paired COM port such as COM3. Do not use the Bluetooth device name.');
        }

        if (self::isSerialPort($printer)) {
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                throw new \InvalidArgumentException('COM ports are only supported on Windows.');
            }

            return new WindowsSerialPrintConnector(self::normalizeSerialPort($printer));
        }

        if (preg_match('/^(\d{1,3}\.){3}\d{1,3}(:\d+)?$/', $printer)) {
            $parts = explode(':', $printer);
            return new NetworkPrintConnector($parts[0], isset($parts[1]) ? (int) $parts[1] : 9100);
        }

        if (str_starts_with($printer, '/dev/')) {
            if (! is_file($printer)) {
                throw new \InvalidArgumentException('The printer device path does not exist: ' . $printer);
            }

            return new FilePrintConnector($printer);
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return new WindowsPrintConnector($printer);
        }

        if (is_file('/dev/usb/lp0')) {
            return new FilePrintConnector('/dev/usb/lp0');
        }

        throw new \InvalidArgumentException('Unsupported printer. Use an IP address, COM port, device path, or Windows shared printer name.');
    }

    private static function isSerialPort(string $printer): bool
    {
        return (bool) preg_match('/^(?:\\\\\\.\\\\)?(COM\d+):?$/i', trim($printer));
    }

    private static function normalizeSerialPort(string $printer): string
    {
        if (preg_match('/^(?:\\\\\\.\\\\)?(COM\d+):?$/i', trim($printer), $match)) {
            return strtoupper($match[1]);
        }

        return strtoupper(trim($printer));
    }

    private static function isPlaceholder(string $printer): bool
    {
        return in_array(strtolower($printer), [
            'no printers found',
            'no bt devices',
            'no bt devices found',
            'no com ports',
            'no com ports found',
            'error loading printers',
            'error scanning bt',
            'error scanning com',
        ], true);
    }
}
