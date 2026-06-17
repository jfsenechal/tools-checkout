@php
    $is32x57 = $size === '32x57';
    $pageWidth = $is32x57 ? '57mm' : '25mm';
    $pageHeight = $is32x57 ? '32mm' : '25mm';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étiquette QR - {{ $tool->name }}</title>
    <style>
        @page {
            size: {{ $pageWidth }} {{ $pageHeight }};
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
        }

        .no-print {
            padding: 16px;
            text-align: center;
            background: #f3f4f6;
            border-bottom: 1px solid #d1d5db;
        }

        .no-print button {
            padding: 8px 24px;
            font-size: 14px;
            cursor: pointer;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            margin: 0 8px;
        }

        .no-print a {
            padding: 8px 24px;
            font-size: 14px;
            color: #2563eb;
            text-decoration: none;
        }

        .label {
            width: {{ $pageWidth }};
            height: {{ $pageHeight }};
            padding: 1mm;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2mm;
            overflow: hidden;
        }

        .label.layout-row {
            flex-direction: row;
        }

        .label.layout-stack {
            flex-direction: column;
        }

        .label img {
            height: 100%;
            width: auto;
            object-fit: contain;
            flex-shrink: 0;
        }

        .label .text {
            flex: 1;
            min-width: 0;
            text-align: left;
            overflow: hidden;
        }

        .label .text .name {
            font-size: 9px;
            font-weight: bold;
            line-height: 1.15;
            word-break: break-word;
        }

        .label .text .category {
            font-size: 7px;
            color: #555;
            margin-top: 0.5mm;
            word-break: break-word;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">Imprimer ({{ $size }} mm)</button>
    <a href="{{ url('/admin') }}">Retour</a>
</div>

<div class="label {{ $is32x57 ? 'layout-row' : 'layout-stack' }}">
    <img src="{{ $tool->qr_code_url }}" alt="QR {{ $tool->name }}">
    @if($is32x57)
        <div class="text">
            <div class="name">{{ $tool->name }}</div>
            @if($tool->category)
                <div class="category">{{ $tool->category->name }}</div>
            @endif
        </div>
    @endif
</div>

</body>
</html>
