@extends('layouts.app')

@section('title', 'Ventas')

@section('content')
    @php
        $fechaHoy = now()->toDateString();
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <h1 class="page-title">Ventas y bajas de balatas</h1>
        <a href="{{ route('ventas.create', ['fecha' => $fecha !== '' ? $fecha : $fechaHoy]) }}" class="btn btn-primary btn-mobile-block">Registrar venta</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('ventas.index') }}" class="search-form">
                <div class="row g-2">
                    <div class="col-12 col-md-3">
                        <input type="date" name="fecha" value="{{ $fecha }}" class="form-control">
                    </div>
                    <div class="col-12 col-md">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            class="form-control"
                            placeholder="Buscar por codigo, marca o nota"
                        >
                    </div>
                    <div class="col-4 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
                    </div>
                    <div class="col-4 col-md-auto">
                        <a href="{{ route('ventas.index', ['fecha' => $fechaHoy]) }}" class="btn btn-outline-secondary w-100">Hoy</a>
                    </div>
                    <div class="col-4 col-md-auto">
                        <a href="{{ route('ventas.index', ['fecha' => '']) }}" class="btn btn-outline-secondary w-100">Todo</a>
                    </div>
                </div>
            </form>
            <div class="small text-muted mt-2">{{ $ventas->count() }} venta(s)</div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Piezas vendidas</div>
                    <div class="h5 mb-0">{{ number_format($totales['piezas']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Total venta</div>
                    <div class="h5 mb-0">${{ number_format($totales['venta'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">Costo inventario</div>
                    <div class="h5 mb-0">${{ number_format($totales['costo'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 border-success-subtle">
                <div class="card-body">
                    <div class="small text-muted">Utilidad</div>
                    <div class="h5 mb-0 text-success">${{ number_format($totales['utilidad'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-md-none">
        @forelse ($ventas as $venta)
            @php
                $totalVenta = (float) $venta->cantidad * (float) $venta->precio_venta_unitario;
                $totalCosto = (float) $venta->cantidad * (float) $venta->precio_inventario_unitario;
                $utilidad = $totalVenta - $totalCosto;
            @endphp
            <div class="mobile-card shadow-sm p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="label">Codigo</div>
                        <div class="value fw-semibold">{{ $venta->codigo_balata }}</div>
                    </div>
                    <div class="text-end">
                        <div class="label">Fecha</div>
                        <div class="value">{{ optional($venta->fecha_venta)->format('Y-m-d') }}</div>
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-6">
                        <div class="label">Marca</div>
                        <div class="value">{{ $venta->marca_balata ?: 'N/A' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="label">Cantidad</div>
                        <div class="value">{{ number_format($venta->cantidad) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="label">Precio inv. unit.</div>
                        <div class="value">${{ number_format((float) $venta->precio_inventario_unitario, 2) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="label">Precio venta unit.</div>
                        <div class="value">${{ number_format((float) $venta->precio_venta_unitario, 2) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="label">Total venta</div>
                        <div class="value fw-semibold">${{ number_format($totalVenta, 2) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="label">Utilidad</div>
                        <div class="value fw-semibold text-success">${{ number_format($utilidad, 2) }}</div>
                    </div>
                </div>

                @if ($venta->nota)
                    <div class="mt-2">
                        <div class="label">Nota</div>
                        <div class="value">{!! nl2br(e($venta->nota)) !!}</div>
                    </div>
                @endif

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('ventas.edit', $venta) }}" class="btn btn-sm btn-outline-primary flex-fill">Editar</a>
                    <form method="POST" action="{{ route('ventas.destroy', ['venta' => $venta, 'fecha' => $fecha]) }}" class="flex-fill" onsubmit="return confirm('Eliminar esta venta? Se restaurara el inventario.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">Eliminar</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="alert alert-light border text-muted">No hay ventas registradas para el filtro seleccionado.</div>
        @endforelse
    </div>

    <div class="card shadow-sm d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Codigo</th>
                    <th>Marca</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">P. inventario</th>
                    <th class="text-end">P. venta</th>
                    <th class="text-end">Total venta</th>
                    <th class="text-end">Utilidad</th>
                    <th>Nota</th>
                    <th class="text-end">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($ventas as $venta)
                    @php
                        $totalVenta = (float) $venta->cantidad * (float) $venta->precio_venta_unitario;
                        $totalCosto = (float) $venta->cantidad * (float) $venta->precio_inventario_unitario;
                        $utilidad = $totalVenta - $totalCosto;
                    @endphp
                    <tr>
                        <td>{{ optional($venta->fecha_venta)->format('Y-m-d') }}</td>
                        <td class="fw-semibold">{{ $venta->codigo_balata }}</td>
                        <td>{{ $venta->marca_balata ?: 'N/A' }}</td>
                        <td class="text-end">{{ number_format($venta->cantidad) }}</td>
                        <td class="text-end">${{ number_format((float) $venta->precio_inventario_unitario, 2) }}</td>
                        <td class="text-end">${{ number_format((float) $venta->precio_venta_unitario, 2) }}</td>
                        <td class="text-end">${{ number_format($totalVenta, 2) }}</td>
                        <td class="text-end text-success fw-semibold">${{ number_format($utilidad, 2) }}</td>
                        <td style="min-width: 220px;">{!! $venta->nota ? nl2br(e($venta->nota)) : '<span class="text-muted small">Sin nota</span>' !!}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('ventas.edit', $venta) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form method="POST" action="{{ route('ventas.destroy', ['venta' => $venta, 'fecha' => $fecha]) }}" class="d-inline" onsubmit="return confirm('Eliminar esta venta? Se restaurara el inventario.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            No hay ventas registradas para el filtro seleccionado.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
