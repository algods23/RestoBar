<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\PrinterConnectorFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Mike42\Escpos\Printer as EscposPrinter;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings', [
            'cashier_printer' => Setting::get('cashier_printer', ''),
            'kitchen_printer' => Setting::get('kitchen_printer', Setting::get('default_printer', '')),
            'cashier_paper_size' => Setting::get('cashier_paper_size', '58mm'),
            'kitchen_paper_size' => Setting::get('kitchen_paper_size', '58mm'),
        ]);
    }

    public function save(Request $request)
    {
        $v = Validator::make($request->all(), [
            'cashier_printer' => ['nullable', 'string'],
            'kitchen_printer' => ['nullable', 'string'],
            'cashier_paper_size' => ['required', 'in:58mm,80mm'],
            'kitchen_paper_size' => ['required', 'in:58mm,80mm'],
        ]);
        $v->validate();

        $cashierPrinter = trim((string) $request->input('cashier_printer'));
        $kitchenPrinter = trim((string) $request->input('kitchen_printer'));
        if ($cashierPrinter !== '' && strcasecmp($cashierPrinter, $kitchenPrinter) === 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['kitchen_printer' => 'Kitchen printer must use a different Bluetooth port from the cashier printer.']);
        }

        Setting::set('cashier_printer', $request->input('cashier_printer'));
        Setting::set('kitchen_printer', $request->input('kitchen_printer'));
        Setting::set('cashier_paper_size', $request->input('cashier_paper_size', '58mm'));
        Setting::set('kitchen_paper_size', $request->input('kitchen_paper_size', '58mm'));
        return redirect()->back()->with('success', 'Settings saved.');
    }

    public function saveKey(Request $request)
    {
        $v = Validator::make($request->all(), [
            'key' => ['required', 'in:cashier_printer,kitchen_printer,cashier_paper_size,kitchen_paper_size'],
            'value' => ['nullable', 'string'],
        ]);
        $v->validate();

        $key = $request->input('key');
        $value = trim((string) $request->input('value'));
        if (in_array($key, ['cashier_printer', 'kitchen_printer'], true) && $value !== '') {
            $otherKey = $key === 'cashier_printer' ? 'kitchen_printer' : 'cashier_printer';
            $otherValue = trim((string) Setting::get($otherKey, ''));
            if ($otherValue !== '' && strcasecmp($value, $otherValue) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'This Bluetooth port is already assigned to the other printer.',
                ], 422);
            }
        }

        Setting::set($request->input('key'), $request->input('value'));
        return response()->json(['success' => true, 'message' => 'Saved']);
    }

    public function testPrint(Request $request)
    {
        $printerType = $request->input('printer_type') === 'cashier' ? 'cashier' : 'kitchen';
        $settingKey = $printerType === 'cashier' ? 'cashier_printer' : 'kitchen_printer';
        $printer = trim((string) $request->input('printer')) ?: (string) Setting::get($settingKey, '');
        if (!$printer) {
            return response()->json(['success' => false, 'message' => 'No ' . $printerType . ' printer configured.'], 422);
        }

        $transport = $request->input('transport', 'auto');
        $paperSize = $request->input('paper_size', Setting::get($printerType . '_paper_size', '58mm'));
        $text = strtoupper($printerType) . " PRINTER TEST\n"
            . "Paper: " . $paperSize . "\n"
            . "Printed at " . date('Y-m-d H:i:s') . "\n";

        try {
            $connector = PrinterConnectorFactory::make($printer, $transport);
            $escpos = new EscposPrinter($connector);
            $escpos->text($text);
            $escpos->cut();
            $escpos->close();
            return response()->json(['success' => true, 'message' => 'Test printed']);
        } catch (\InvalidArgumentException $e) {
            logger()->warning('Settings test print configuration error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            logger()->error('Settings test print failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Print failed: ' . $e->getMessage()], 500);
        }
    }
}
