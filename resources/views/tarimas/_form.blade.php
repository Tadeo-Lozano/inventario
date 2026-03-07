@php
    /** @var \App\Models\Tarima|null $tarima */
    $tarima = $tarima ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="numero_identificacion" class="form-label">Numero de identificacion</label>
        <input type="text" class="form-control" id="numero_identificacion" name="numero_identificacion" value="{{ old('numero_identificacion', $tarima?->numero_identificacion) }}" required>
    </div>
    <div class="col-md-6">
        <label for="imagenes" class="form-label">Imagenes de la tarima</label>
        <input
            type="file"
            class="form-control"
            id="imagenes"
            name="imagenes[]"
            multiple
            accept="image/*"
            capture="environment"
        >
        <div class="form-text">Puedes agregar 0, 1 o varias imagenes. En celular se abrira la camara.</div>
    </div>

    @if ($tarima && $tarima->imagenes->isNotEmpty())
        <div class="col-12">
            <label class="form-label">Imagenes actuales</label>
            <div class="d-flex flex-wrap gap-3">
                @foreach ($tarima->imagenes as $imagen)
                    <label class="border rounded p-2 text-center">
                        <img src="{{ asset('storage/'.$imagen->ruta) }}" alt="Imagen de tarima" class="thumb d-block mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="{{ $imagen->id }}" name="eliminar_imagenes[]" id="eliminar_tarima_{{ $imagen->id }}">
                            <label class="form-check-label small" for="eliminar_tarima_{{ $imagen->id }}">
                                Eliminar
                            </label>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>
    @endif
</div>
