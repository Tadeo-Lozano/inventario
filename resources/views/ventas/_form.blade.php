@php
    /** @var \App\Models\Venta|null $venta */
    $venta = $venta ?? null;
    $fechaDefault = $fecha ?? now()->toDateString();
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label for="fecha_venta" class="form-label">Fecha de venta</label>
        <input
            type="date"
            class="form-control"
            id="fecha_venta"
            name="fecha_venta"
            value="{{ old('fecha_venta', optional($venta?->fecha_venta)->format('Y-m-d') ?? $fechaDefault) }}"
            required
        >
    </div>
    <div class="col-md-8">
        <label for="balata_id" class="form-label">Balata</label>
        <select class="form-select" id="balata_id" name="balata_id" required data-venta-balata-select>
            <option value="">Selecciona una balata</option>
            @foreach ($balatas as $balataOption)
                <option
                    value="{{ $balataOption->id }}"
                    @selected((string) old('balata_id', $venta?->balata_id) === (string) $balataOption->id)
                    data-precio-inventario="{{ number_format((float) $balataOption->precio_inventario, 2, '.', '') }}"
                    data-precio-venta="{{ number_format((float) $balataOption->precio_venta, 2, '.', '') }}"
                    data-existencia="{{ (int) $balataOption->cantidad }}"
                >
                    {{ $balataOption->codigo }} | {{ $balataOption->marca }} | Disp: {{ number_format($balataOption->cantidad) }}
                </option>
            @endforeach
        </select>
        <div id="ventaExistenciaInfo" class="form-text"></div>
    </div>
    <div class="col-md-4">
        <label for="cantidad" class="form-label">Cantidad vendida</label>
        <input type="number" class="form-control" id="cantidad" name="cantidad" value="{{ old('cantidad', $venta?->cantidad) }}" min="1" required>
    </div>
    <div class="col-md-4">
        <label for="precio_inventario_unitario" class="form-label">Precio inventario unitario</label>
        <input
            type="number"
            class="form-control"
            id="precio_inventario_unitario"
            name="precio_inventario_unitario"
            value="{{ old('precio_inventario_unitario', $venta?->precio_inventario_unitario) }}"
            min="0"
            step="0.01"
            required
        >
    </div>
    <div class="col-md-4">
        <label for="precio_venta_unitario" class="form-label">Precio venta unitario</label>
        <input
            type="number"
            class="form-control"
            id="precio_venta_unitario"
            name="precio_venta_unitario"
            value="{{ old('precio_venta_unitario', $venta?->precio_venta_unitario) }}"
            min="0"
            step="0.01"
            required
        >
    </div>
    <div class="col-12">
        <label for="nota" class="form-label">Nota (opcional)</label>
        <textarea class="form-control" id="nota" name="nota" rows="2" placeholder="Ej: venta mostrador, cliente frecuente">{{ old('nota', $venta?->nota) }}</textarea>
    </div>
</div>

<script>
    (function () {
        const select = document.querySelector('[data-venta-balata-select]');
        if (!select) {
            return;
        }

        const info = document.getElementById('ventaExistenciaInfo');
        const precioInventarioInput = document.getElementById('precio_inventario_unitario');
        const precioVentaInput = document.getElementById('precio_venta_unitario');

        function updateStockInfo() {
            const selected = select.options[select.selectedIndex];
            if (!selected || !selected.value) {
                info.textContent = '';
                return;
            }

            const existencia = Number.parseInt(selected.getAttribute('data-existencia') || '0', 10);
            info.textContent = 'Existencia actual: ' + existencia.toLocaleString('es-MX') + ' pieza(s).';
        }

        select.addEventListener('change', function () {
            const selected = select.options[select.selectedIndex];
            if (!selected || !selected.value) {
                return;
            }

            const precioInventario = selected.getAttribute('data-precio-inventario');
            const precioVenta = selected.getAttribute('data-precio-venta');

            if (precioInventario !== null) {
                precioInventarioInput.value = precioInventario;
            }

            if (precioVenta !== null) {
                precioVentaInput.value = precioVenta;
            }

            updateStockInfo();
        });

        updateStockInfo();
    })();
</script>
