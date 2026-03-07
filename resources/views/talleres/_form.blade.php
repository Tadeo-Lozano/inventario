@php
    /** @var \App\Models\Taller|null $taller */
    $taller = $taller ?? null;
    $seleccionadas = old('balatas', $taller?->balatas->pluck('id')->all() ?? []);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre del taller</label>
        <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre', $taller?->nombre) }}" required>
    </div>
    <div class="col-md-6">
        <label for="ubicacion" class="form-label">Ubicacion</label>
        <input type="text" class="form-control" id="ubicacion" name="ubicacion" value="{{ old('ubicacion', $taller?->ubicacion) }}" required>
    </div>
    <div class="col-12">
        <label class="form-label">Balatas de interes</label>
        @if ($balatas->isEmpty())
            <div class="text-muted small">Aun no hay balatas registradas.</div>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-2">
                @foreach ($balatas as $balata)
                    <div class="col">
                        <label class="form-check border rounded p-2 h-100">
                            <input
                                class="form-check-input me-2"
                                type="checkbox"
                                name="balatas[]"
                                value="{{ $balata->id }}"
                                @checked(in_array($balata->id, $seleccionadas))
                            >
                            <span class="form-check-label">
                                <strong>{{ $balata->codigo }}</strong><br>
                                <span class="small text-muted">{{ $balata->marca }} | {{ $balata->calidad }} | Venta ${{ number_format((float) $balata->precio_venta, 2) }}</span>
                            </span>
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
