@extends('layouts.app')

@section('title', 'Tarimas')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <h1 class="page-title">Tarimas</h1>
        <a href="{{ route('tarimas.create') }}" class="btn btn-primary btn-mobile-block">Nueva tarima</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('tarimas.index') }}" class="search-form">
                <div class="row g-2">
                    <div class="col-12 col-md">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            class="form-control"
                            placeholder="Buscar tarima por numero o por articulo almacenado"
                        >
                    </div>
                    <div class="col-6 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Buscar</button>
                    </div>
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('tarimas.index') }}" class="btn btn-outline-secondary w-100">Limpiar</a>
                    </div>
                </div>
            </form>
            <div class="small text-muted mt-2">{{ $tarimas->count() }} resultado(s)</div>
        </div>
    </div>

    <div class="d-md-none">
        @forelse ($tarimas as $tarima)
            @php
                $galeriaTarima = $tarima->imagenes->map(fn ($imagen) => asset('storage/'.$imagen->ruta))->values();
                $balatasTarima = $tarima->balatas->map(fn ($balata) => [
                    'codigo' => $balata->codigo,
                    'marca' => $balata->marca,
                    'calidad' => $balata->calidad,
                    'cantidad' => (int) $balata->cantidad,
                    'precio_inventario' => (float) $balata->precio_inventario,
                    'precio_venta' => (float) $balata->precio_venta,
                ])->values();
                if ($galeriaTarima->isEmpty() && $tarima->foto) {
                    $galeriaTarima = collect([asset('storage/'.$tarima->foto)]);
                }
            @endphp
            <div class="mobile-card shadow-sm p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="label">Numero</div>
                        <button
                            type="button"
                            class="btn btn-link p-0 fw-semibold text-decoration-none"
                            data-tarima-numero="{{ $tarima->numero_identificacion }}"
                            data-tarima-balatas='@json($balatasTarima)'
                        >
                            {{ $tarima->numero_identificacion }}
                        </button>
                    </div>
                    <div class="text-end">
                        <div class="label">Balatas</div>
                        <div class="value fw-semibold">{{ $tarima->balatas_count }}</div>
                    </div>
                </div>

                <div class="mt-2">
                    <div class="label">Imagenes</div>
                    @if ($galeriaTarima->isEmpty())
                        <div class="value text-muted small">Sin imagen</div>
                    @else
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($galeriaTarima as $url)
                                <button
                                    type="button"
                                    class="p-0 border-0 bg-transparent"
                                    data-gallery-images='@json($galeriaTarima)'
                                    data-gallery-start="{{ $loop->index }}"
                                >
                                    <img src="{{ $url }}" alt="Imagen de tarima" class="thumb">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('tarimas.edit', $tarima) }}" class="btn btn-sm btn-outline-primary flex-fill">Editar</a>
                    <form method="POST" action="{{ route('tarimas.destroy', $tarima) }}" class="flex-fill" onsubmit="return confirm('Eliminar esta tarima?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">Eliminar</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="alert alert-light border text-muted">No hay tarimas registradas.</div>
        @endforelse
    </div>

    <div class="card shadow-sm d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Numero</th>
                    <th>Imagenes</th>
                    <th class="text-end">Balatas asignadas</th>
                    <th class="text-end">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($tarimas as $tarima)
                    @php
                        $galeriaTarima = $tarima->imagenes->map(fn ($imagen) => asset('storage/'.$imagen->ruta))->values();
                        $balatasTarima = $tarima->balatas->map(fn ($balata) => [
                            'codigo' => $balata->codigo,
                            'marca' => $balata->marca,
                            'calidad' => $balata->calidad,
                            'cantidad' => (int) $balata->cantidad,
                            'precio_inventario' => (float) $balata->precio_inventario,
                            'precio_venta' => (float) $balata->precio_venta,
                        ])->values();
                        if ($galeriaTarima->isEmpty() && $tarima->foto) {
                            $galeriaTarima = collect([asset('storage/'.$tarima->foto)]);
                        }
                    @endphp
                    <tr>
                        <td>
                            <button
                                type="button"
                                class="btn btn-link p-0 fw-semibold text-decoration-none"
                                data-tarima-numero="{{ $tarima->numero_identificacion }}"
                                data-tarima-balatas='@json($balatasTarima)'
                            >
                                {{ $tarima->numero_identificacion }}
                            </button>
                        </td>
                        <td>
                            @if ($galeriaTarima->isEmpty())
                                <span class="text-muted small">Sin imagen</span>
                            @else
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($galeriaTarima as $url)
                                        <button
                                            type="button"
                                            class="p-0 border-0 bg-transparent"
                                            data-gallery-images='@json($galeriaTarima)'
                                            data-gallery-start="{{ $loop->index }}"
                                        >
                                            <img src="{{ $url }}" alt="Imagen de tarima" class="thumb">
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="text-end">{{ $tarima->balatas_count }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('tarimas.edit', $tarima) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form method="POST" action="{{ route('tarimas.destroy', $tarima) }}" class="d-inline" onsubmit="return confirm('Eliminar esta tarima?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            No hay tarimas registradas.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="tarimaBalatasModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="h5 mb-0" id="tarimaBalatasTitle">Balatas en tarima</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="tarimaBalatasEmpty" class="text-muted d-none">Esta tarima no tiene balatas asignadas.</div>
                    <div id="tarimaBalatasTableWrap" class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>Codigo</th>
                                <th>Marca</th>
                                <th>Calidad</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Precio inventario</th>
                                <th class="text-end">Precio venta</th>
                            </tr>
                            </thead>
                            <tbody id="tarimaBalatasBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-tarima-balatas]');
            if (!trigger) {
                return;
            }

            event.preventDefault();

            let balatas = [];
            try {
                balatas = JSON.parse(trigger.getAttribute('data-tarima-balatas') || '[]');
            } catch (error) {
                balatas = [];
            }

            const numeroTarima = trigger.getAttribute('data-tarima-numero') || '';
            const title = document.getElementById('tarimaBalatasTitle');
            const empty = document.getElementById('tarimaBalatasEmpty');
            const tableWrap = document.getElementById('tarimaBalatasTableWrap');
            const body = document.getElementById('tarimaBalatasBody');

            title.textContent = numeroTarima ? ('Balatas en tarima ' + numeroTarima) : 'Balatas en tarima';
            body.innerHTML = '';

            if (!Array.isArray(balatas) || balatas.length === 0) {
                empty.classList.remove('d-none');
                tableWrap.classList.add('d-none');
            } else {
                empty.classList.add('d-none');
                tableWrap.classList.remove('d-none');

                const formatMoney = function (value) {
                    const amount = Number(value || 0);
                    return amount.toLocaleString('es-MX', {
                        style: 'currency',
                        currency: 'MXN',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                };

                balatas.forEach(function (balata) {
                    const row = document.createElement('tr');

                    const codigo = document.createElement('td');
                    codigo.className = 'fw-semibold';
                    codigo.textContent = balata.codigo || 'N/A';
                    row.appendChild(codigo);

                    const marca = document.createElement('td');
                    marca.textContent = balata.marca || 'N/A';
                    row.appendChild(marca);

                    const calidad = document.createElement('td');
                    calidad.textContent = balata.calidad || 'N/A';
                    row.appendChild(calidad);

                    const cantidad = document.createElement('td');
                    cantidad.className = 'text-end';
                    cantidad.textContent = Number(balata.cantidad || 0).toLocaleString('es-MX');
                    row.appendChild(cantidad);

                    const precioInventario = document.createElement('td');
                    precioInventario.className = 'text-end';
                    precioInventario.textContent = formatMoney(balata.precio_inventario);
                    row.appendChild(precioInventario);

                    const precioVenta = document.createElement('td');
                    precioVenta.className = 'text-end';
                    precioVenta.textContent = formatMoney(balata.precio_venta);
                    row.appendChild(precioVenta);

                    body.appendChild(row);
                });
            }

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('tarimaBalatasModal'));
            modal.show();
        });
    </script>
@endsection
