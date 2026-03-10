@php
    /** @var \App\Models\Taller|null $taller */
    $taller = $taller ?? null;
    $seleccionadas = old('balatas', $taller?->balatas->pluck('id')->all() ?? []);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre del taller o encargado</label>
        <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre', $taller?->nombre) }}" required>
    </div>
    <div class="col-md-6">
        <label for="telefono" class="form-label">Numero de telefono</label>
        <input type="text" class="form-control" id="telefono" name="telefono" value="{{ old('telefono', $taller?->telefono) }}" required>
    </div>
    <div class="col-md-4">
        <label for="calle" class="form-label">Calle</label>
        <input type="text" class="form-control" id="calle" name="calle" value="{{ old('calle', $taller?->calle) }}" required>
    </div>
    <div class="col-md-4">
        <label for="colonia" class="form-label">Colonia</label>
        <input type="text" class="form-control" id="colonia" name="colonia" value="{{ old('colonia', $taller?->colonia) }}" required>
    </div>
    <div class="col-md-4">
        <label for="numero" class="form-label">Numero</label>
        <input type="text" class="form-control" id="numero" name="numero" value="{{ old('numero', $taller?->numero) }}" required>
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
