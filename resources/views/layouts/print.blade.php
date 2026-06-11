<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: monospace;
            font-size: 10px;
            background: #fff;
            display: flex;
            justify-content: center;
        }

        #receipt-wrapper {
            width: 58mm;
            max-width: 58mm;
        }

        @media print {
            @page {
                size: 58mm auto;
                margin: 0;
            }

            html, body {
                width: 58mm;
                max-width: 58mm;
                margin: 0 auto;
            }

            #receipt-wrapper {
                width: 58mm;
                max-width: 58mm;
                margin: 0;
                padding: 2mm;
            }
        }
    </style>
</head>
<body>
    <div id="receipt-wrapper">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>