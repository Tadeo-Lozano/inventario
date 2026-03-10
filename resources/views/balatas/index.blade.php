@extends('layouts.app')

@section('title', 'Balatas')

@section('content')
    @php
        $pdfQuery = $search !== '' ? ['search' => $search] : [];
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <h1 class="page-title">Inventario de balatas</h1>
        <div class="d-flex flex-column flex-sm-row gap-2">
            <a href="{{ route('balatas.export.pdf', $pdfQuery) }}" class="btn btn-outline-danger btn-mobile-block">
                Exportar PDF
            </a>
            <a href="{{ route('balatas.create') }}" class="btn btn-primary btn-mobile-block">Nueva balata</a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('balatas.index') }}" class="search-form">
                <div class="row g-2">
                    <div class="col-12 col-md">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            class="form-control"
                            placeholder="Buscar articulo por codigo, marca, calidad, posicion, vehiculo, tarima o taller"
                        >
                    </div>
                    <div class="col-6 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Buscar</button>
                    </div>
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('balatas.index') }}" class="btn btn-outline-secondary w-100">Limpiar</a>
                    </div>
                </div>
            </form>
            <div class="small text-muted mt-2">{{ $balatas->count() }} resultado(s)</div>
        </div>
    </div>

    <div class="d-md-none">
        @forelse ($balatas as $balata)
            @php
                $galeriaBalata = $balata->imagenes->map(fn ($imagen) => route('media.show', ['path' => $imagen->ruta]))->values();
            @endphp
            <div class="mobile-card shadow-sm p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="label">Codigo</div>
                        <div class="value fw-semibold">{{ $balata->codigo }}</div>
                    </div>
                    <div class="text-end">
                        <div class="label">Precio venta</div>
                        <div class="value fw-semibold">${{ number_format((float) $balata->precio_venta, 2) }}</div>
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-6">
                        <div class="label">Marca</div>
                        <div class="value">{{ $balata->marca }}</div>
                    </div>
                    <div class="col-6">
                        <div class="label">Calidad</div>
                        <div class="value">{{ $balata->calidad }}</div>
                    </div>
                    <div class="col-6">
                        <div class="label">Posicion</div>
                        <div class="value">{{ $balata->posicion }}</div>
                    </div>
                    <div class="col-6">
                        <div class="label">Cantidad</div>
                        <div class="value">{{ number_format($balata->cantidad) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="label">Tarima</div>
                        <div class="value">{{ $balata->tarima?->numero_identificacion ?? 'N/A' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="label">Precio inventario</div>
                        <div class="value">${{ number_format((float) $balata->precio_inventario, 2) }}</div>
                    </div>
                </div>

                <div class="mt-2">
                    <div class="label">Vehiculos</div>
                    <div class="value">{!! nl2br(e($balata->vehiculos)) !!}</div>
                </div>

                <div class="mt-2">
                    <div class="label">Imagenes</div>
                    @if ($galeriaBalata->isEmpty())
                        <div class="value text-muted small">Sin imagen</div>
                    @else
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($galeriaBalata as $url)
                                <button
                                    type="button"
                                    class="p-0 border-0 bg-transparent"
                                    data-gallery-images='@json($galeriaBalata)'
                                    data-gallery-start="{{ $loop->index }}"
                                >
                                    <img src="{{ $url }}" alt="Imagen de balata" class="thumb">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="mt-2">
                    <div class="label">Talleres interesados</div>
                    @if ($balata->talleres->isEmpty())
                        <div class="value text-muted small">Ninguno</div>
                    @else
                        <div class="d-flex flex-wrap gap-1">
                            @foreach ($balata->talleres as $taller)
                                <span class="badge text-bg-secondary">{{ $taller->nombre }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('balatas.edit', $balata) }}" class="btn btn-sm btn-outline-primary flex-fill">Editar</a>
                    <form method="POST" action="{{ route('balatas.destroy', $balata) }}" class="flex-fill" onsubmit="return confirm('Eliminar esta balata?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">Eliminar</button>
                    </form>
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
                    <th>Calidad</th>
                    <th>Posicion</th>
                    <th>Vehiculos</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Precio inventario</th>
                    <th class="text-end">Precio venta</th>
                    <th>Tarima</th>
                    <th>Imagenes</th>
                    <th>Talleres interesados</th>
                    <th class="text-end">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($balatas as $balata)
                    @php
                        $galeriaBalata = $balata->imagenes->map(fn ($imagen) => route('media.show', ['path' => $imagen->ruta]))->values();
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $balata->codigo }}</td>
                        <td>{{ $balata->marca }}</td>
                        <td>{{ $balata->calidad }}</td>
                        <td>{{ $balata->posicion }}</td>
                        <td style="min-width: 240px;">{!! nl2br(e($balata->vehiculos)) !!}</td>
                        <td class="text-end">{{ number_format($balata->cantidad) }}</td>
                        <td class="text-end">${{ number_format((float) $balata->precio_inventario, 2) }}</td>
                        <td class="text-end">${{ number_format((float) $balata->precio_venta, 2) }}</td>
                        <td>{{ $balata->tarima?->numero_identificacion ?? 'N/A' }}</td>
                        <td>
                            @if ($galeriaBalata->isEmpty())
                                <span class="text-muted small">Sin imagen</span>
                            @else
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($galeriaBalata as $url)
                                        <button
                                            type="button"
                                            class="p-0 border-0 bg-transparent"
                                            data-gallery-images='@json($galeriaBalata)'
                                            data-gallery-start="{{ $loop->index }}"
                                        >
                                            <img src="{{ $url }}" alt="Imagen de balata" class="thumb">
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>
                            @if ($balata->talleres->isEmpty())
                                <span class="text-muted small">Ninguno</span>
                            @else
                                @foreach ($balata->talleres as $taller)
                                    <span class="badge text-bg-secondary mb-1">{{ $taller->nombre }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('balatas.edit', $balata) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form method="POST" action="{{ route('balatas.destroy', $balata) }}" class="d-inline" onsubmit="return confirm('Eliminar esta balata?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center py-4 text-muted">
                            No hay balatas registradas.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
