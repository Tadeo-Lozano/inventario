@extends('layouts.app')

@section('title', 'Talleres')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <h1 class="page-title">Talleres</h1>
        <a href="{{ route('talleres.create') }}" class="btn btn-primary btn-mobile-block">Nuevo taller</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('talleres.index') }}" class="search-form">
                <div class="row g-2">
                    <div class="col-12 col-md">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            class="form-control"
                            placeholder="Buscar taller por nombre, ubicacion o balata de interes"
                        >
                    </div>
                    <div class="col-6 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Buscar</button>
                    </div>
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('talleres.index') }}" class="btn btn-outline-secondary w-100">Limpiar</a>
                    </div>
                </div>
            </form>
            <div class="small text-muted mt-2">{{ $talleres->count() }} resultado(s)</div>
        </div>
    </div>

    <div class="d-md-none">
        @forelse ($talleres as $taller)
            <div class="mobile-card shadow-sm p-3 mb-3">
                <div>
                    <div class="label">Nombre</div>
                    <div class="value fw-semibold">{{ $taller->nombre }}</div>
                </div>
                <div class="mt-2">
                    <div class="label">Ubicacion</div>
                    <div class="value">{{ $taller->ubicacion }}</div>
                </div>
                <div class="mt-2">
                    <div class="label">Balatas de interes</div>
                    @if ($taller->balatas->isEmpty())
                        <div class="value text-muted small">Sin balatas asignadas</div>
                    @else
                        <div class="d-flex flex-wrap gap-1">
                            @foreach ($taller->balatas as $balata)
                                <span class="badge text-bg-secondary">{{ $balata->codigo }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('talleres.edit', $taller) }}" class="btn btn-sm btn-outline-primary flex-fill">Editar</a>
                    <form method="POST" action="{{ route('talleres.destroy', $taller) }}" class="flex-fill" onsubmit="return confirm('Eliminar este taller?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">Eliminar</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="alert alert-light border text-muted">No hay talleres registrados.</div>
        @endforelse
    </div>

    <div class="card shadow-sm d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Ubicacion</th>
                    <th>Balatas de interes</th>
                    <th class="text-end">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($talleres as $taller)
                    <tr>
                        <td class="fw-semibold">{{ $taller->nombre }}</td>
                        <td>{{ $taller->ubicacion }}</td>
                        <td>
                            @if ($taller->balatas->isEmpty())
                                <span class="text-muted small">Sin balatas asignadas</span>
                            @else
                                @foreach ($taller->balatas as $balata)
                                    <span class="badge text-bg-secondary mb-1">{{ $balata->codigo }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('talleres.edit', $taller) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            <form method="POST" action="{{ route('talleres.destroy', $taller) }}" class="d-inline" onsubmit="return confirm('Eliminar este taller?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            No hay talleres registrados.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
