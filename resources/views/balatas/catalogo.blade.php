@extends('layouts.app')

@section('title', 'Catalogo de existencias')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <h1 class="page-title">Catalogo de existencias</h1>
        <a href="{{ route('balatas.index') }}" class="btn btn-outline-primary btn-mobile-block">Gestionar balatas</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="small text-muted">
                {{ $balatas->count() }} balata(s) registradas | {{ number_format($balatas->sum('cantidad')) }} pieza(s) en inventario
            </div>
        </div>
    </div>

    <div class="d-md-none">
        @forelse ($balatas as $balata)
            <div class="mobile-card shadow-sm p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="label">Codigo</div>
                        <div class="value fw-semibold">{{ $balata->codigo }}</div>
                    </div>
                    <div class="text-end">
                        <div class="label">Tarima</div>
                        <div class="value">{{ $balata->tarima?->numero_identificacion ?? 'N/A' }}</div>
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-6">
                        <div class="label">Marca</div>
                        <div class="value">{{ $balata->marca }}</div>
                    </div>
                    <div class="col-6">
                        <div class="label">Cantidad</div>
                        <div class="value">{{ number_format($balata->cantidad) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="label">Precio inventario</div>
                        <div class="value">${{ number_format((float) $balata->precio_inventario, 2) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="label">Precio venta</div>
                        <div class="value fw-semibold">${{ number_format((float) $balata->precio_venta, 2) }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-light border text-muted">No hay balatas registradas.</div>
        @endforelse
    </div>

    <div class="card shadow-sm d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Codigo</th>
                    <th>Marca</th>
                    <th>Tarima</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Precio inventario</th>
                    <th class="text-end">Precio venta</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($balatas as $balata)
                    <tr>
                        <td class="fw-semibold">{{ $balata->codigo }}</td>
                        <td>{{ $balata->marca }}</td>
                        <td>{{ $balata->tarima?->numero_identificacion ?? 'N/A' }}</td>
                        <td class="text-end">{{ number_format($balata->cantidad) }}</td>
                        <td class="text-end">${{ number_format((float) $balata->precio_inventario, 2) }}</td>
                        <td class="text-end">${{ number_format((float) $balata->precio_venta, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No hay balatas registradas.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection