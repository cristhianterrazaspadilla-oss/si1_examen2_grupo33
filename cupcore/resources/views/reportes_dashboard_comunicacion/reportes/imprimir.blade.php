<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportData['label'] }} | CUPCore</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 18px;
            color: #111827;
            background: #ffffff;
        }

        .page {
            background: #ffffff;
            padding: 0;
        }

        .meta,
        .filters {
            margin-top: 16px;
        }

        .badge {
            display: inline-block;
            margin: 4px 8px 0 0;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            font-size: 12px;
            color: #334155;
            background: #f8fafc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
            font-size: 13px;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #e2e8f0;
        }

        .warning {
            margin-top: 16px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .empty {
            margin-top: 24px;
            padding: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
        }

        @page {
            margin: 18px;
        }
    </style>
</head>
<body>
    <div class="page">
        <header>
            <h1 style="margin: 0; font-size: 28px;">CUPCore</h1>
            <p style="margin: 8px 0 0; color: #475569;">CU16 Reportes y Dashboard Academico</p>
            <h2 style="margin: 20px 0 8px; font-size: 24px;">{{ $reportData['label'] }}</h2>
            <div class="meta">
                <strong>Fecha de generacion:</strong> {{ $generatedAt->format('Y-m-d H:i') }}
            </div>
            <div class="meta">
                <strong>Total de registros:</strong> {{ $reportData['total'] }}
            </div>
            <div class="filters">
                <strong>Filtros aplicados:</strong>
                <div>
                    @forelse ($appliedFilters as $label => $value)
                        <span class="badge">{{ $label }}: {{ $value }}</span>
                    @empty
                        <span class="badge">Sin filtros adicionales</span>
                    @endforelse
                </div>
            </div>
        </header>

        @if ($reportData['was_limited'])
            <div class="warning">
                El PDF incluye solo los primeros {{ $reportData['limit'] }} registros. El reporte completo excede ese limite.
            </div>
        @endif

        @if ($reportData['rows']->count() === 0)
            <div class="empty">
                No existen datos para los filtros seleccionados.
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        @foreach ($reportData['columns'] as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportData['rows'] as $row)
                        <tr>
                            @foreach (array_keys($reportData['columns']) as $columnKey)
                                <td>{{ $row[$columnKey] ?? '' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

    </div>
</body>
</html>
