<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Codes - Outils & Travailleurs</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
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

        .section-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            padding: 8px 0 4px;
            border-bottom: 2px solid #000;
            margin-bottom: 4px;
            page-break-after: avoid;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 4px;
            padding: 4px 0;
        }

        .card {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 6px;
            text-align: center;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .card img {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }

        .card .label {
            font-size: 10px;
            font-weight: bold;
            line-height: 1.2;
            word-break: break-word;
            max-width: 100%;
        }

        .card .sublabel {
            font-size: 8px;
            color: #555;
        }

        .page-break {
            page-break-before: always;
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
    <button onclick="window.print()">Imprimer</button>
    <a href="{{ url('/admin') }}">Retour</a>
</div>

@if($tools->isNotEmpty())
    <div class="section-title">Outils ({{ $tools->count() }})</div>
    <div class="grid">
        @foreach($tools as $tool)
            <div class="card">
                <img src="{{ $tool->qr_code_url }}" alt="QR {{ $tool->name }}">
                <div class="label">{{ $tool->name }}</div>
                @if($tool->category)
                    <div class="sublabel">{{ $tool->category->name }}</div>
                @endif
            </div>
        @endforeach
    </div>
@endif

@if($workers->isNotEmpty())
    <div class="{{ $tools->isNotEmpty() ? 'page-break' : '' }}">
        <div class="section-title">Travailleurs ({{ $workers->count() }})</div>
        <div class="grid">
            @foreach($workers as $worker)
                <div class="card">
                    <img src="{{ $worker->qr_code_url }}" alt="QR {{ $worker->first_name }} {{ $worker->last_name }}">
                    <div class="label">{{ $worker->last_name }} {{ $worker->first_name }}</div>
                </div>
            @endforeach
        </div>
    </div>
@endif

</body>
</html>
