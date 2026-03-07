@php
    /** @var \App\Models\Balata|null $balata */
    $balata = $balata ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-3">
        <label for="codigo" class="form-label">Codigo</label>
        <input type="text" class="form-control" id="codigo" name="codigo" value="{{ old('codigo', $balata?->codigo) }}" required>
    </div>
    <div class="col-md-3">
        <label for="marca" class="form-label">Marca</label>
        <input type="text" class="form-control" id="marca" name="marca" value="{{ old('marca', $balata?->marca) }}" placeholder="Ej: Brembo, Bosch, Raybestos" required>
    </div>
    <div class="col-md-3">
        <label for="calidad" class="form-label">Calidad</label>
        <input type="text" class="form-control" id="calidad" name="calidad" value="{{ old('calidad', $balata?->calidad) }}" placeholder="Ej: Estandar, Premium, Ceramica" required>
    </div>
    <div class="col-md-3">
        <label for="tarima_id" class="form-label">Tarima</label>
        <select class="form-select" id="tarima_id" name="tarima_id" required>
            <option value="">Selecciona una tarima</option>
            @foreach ($tarimas as $tarima)
                <option value="{{ $tarima->id }}" @selected((string) old('tarima_id', $balata?->tarima_id) === (string) $tarima->id)>
                    {{ $tarima->numero_identificacion }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="cantidad" class="form-label">Cantidad</label>
        <input type="number" class="form-control" id="cantidad" name="cantidad" value="{{ old('cantidad', $balata?->cantidad) }}" min="0" required>
    </div>
    <div class="col-md-4">
        <label for="precio_inventario" class="form-label">Precio inventario</label>
        <input type="number" class="form-control" id="precio_inventario" name="precio_inventario" value="{{ old('precio_inventario', $balata?->precio_inventario) }}" min="0" step="0.01" required>
    </div>
    <div class="col-md-4">
        <label for="precio_venta" class="form-label">Precio venta</label>
        <input type="number" class="form-control" id="precio_venta" name="precio_venta" value="{{ old('precio_venta', $balata?->precio_venta) }}" min="0" step="0.01" required>
    </div>
    <div class="col-12">
        <label for="vehiculos" class="form-label">Vehiculos que aplica</label>
        <textarea class="form-control" id="vehiculos" name="vehiculos" rows="3" placeholder="Ejemplo: Nissan Versa 2019-2022, Chevrolet Aveo 2018-2021" required>{{ old('vehiculos', $balata?->vehiculos) }}</textarea>
    </div>
    <div class="col-12">
        <label for="imagenes" class="form-label">Imagenes de la balata</label>
        <input
            type="file"
            class="form-control"
            id="imagenes"
            name="imagenes[]"
            multiple
            accept="image/*"
            capture="environment"
        >
        <div class="form-text">
            Puedes agregar 0, 1 o varias imagenes. En celular se abrira la camara.
        </div>
    </div>

    @if ($balata && $balata->imagenes->isNotEmpty())
        <div class="col-12">
            <label class="form-label">Imagenes actuales</label>
            <div class="d-flex flex-wrap gap-3">
                @foreach ($balata->imagenes as $imagen)
                    <label class="border rounded p-2 text-center">
                        <img src="{{ route('media.show', ['path' => $imagen->ruta]) }}" alt="Imagen de balata" class="thumb d-block mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="{{ $imagen->id }}" name="eliminar_imagenes[]" id="eliminar_{{ $imagen->id }}">
                            <label class="form-check-label small" for="eliminar_{{ $imagen->id }}">
                                Eliminar
                            </label>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>
    @endif
</div>
