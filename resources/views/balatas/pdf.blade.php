<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de balatas</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            color: #111827;
            font-size: 11px;
            margin: 20px;
        }

        .header {
            margin-bottom: 14px;
        }

        .header h1 {
            font-size: 18px;
            margin: 0 0 6px 0;
        }

        .meta {
            color: #4b5563;
            font-size: 10px;
            margin: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-size: 10px;
            text-transform: uppercase;
        }

        .text-end {
            text-align: right;
            white-space: nowrap;
        }

        .small {
            color: #6b7280;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventario de balatas</h1>
        <p class="meta">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</p>
        <p class="meta">Total articulos: {{ $balatas->count() }} | Total piezas: {{ number_format($totalPiezas) }}</p>
        @if ($search !== '')
            <p class="meta">Filtro aplicado: "{{ $search }}"</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Codigo</th>
                <th>Marca</th>
                <th>Calidad</th>
                <th>Posicion</th>
                <th>Vehiculos</th>
                <th class="text-end">Cantidad</th>
                <th class="text-end">Precio inventario</th>
                <th class="text-end">Precio venta</th>
                <th>Tarima</th>
                <th>Talleres interesados</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($balatas as $balata)
                <tr>
                    <td>{{ $balata->codigo }}</td>
                    <td>{{ $balata->marca }}</td>
                    <td>{{ $balata->calidad }}</td>
                    <td>{{ $balata->posicion }}</td>
                    <td>{{ str_replace(["\r\n", "\r", "\n"], ', ', $balata->vehiculos) }}</td>
                    <td class="text-end">{{ number_format($balata->cantidad) }}</td>
                    <td class="text-end">${{ number_format((float) $balata->precio_inventario, 2) }}</td>
                    <td class="text-end">${{ number_format((float) $balata->precio_venta, 2) }}</td>
                    <td>{{ $balata->tarima?->numero_identificacion ?? 'N/A' }}</td>
                    <td>{{ $balata->talleres->pluck('nombre')->join(', ') ?: 'Ninguno' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="small">No hay balatas para exportar con el filtro actual.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
