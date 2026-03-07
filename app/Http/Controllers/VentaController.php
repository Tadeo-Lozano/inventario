<?php

namespace App\Http\Controllers;

use App\Models\Balata;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $fecha = trim((string) $request->query('fecha', now()->toDateString()));
        $search = trim((string) $request->query('search', ''));

        $ventas = Venta::query()
            ->with('balata:id,codigo,marca')
            ->when($fecha !== '', function ($query) use ($fecha) {
                $query->whereDate('fecha_venta', $fecha);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('codigo_balata', 'like', "%{$search}%")
                        ->orWhere('marca_balata', 'like', "%{$search}%")
                        ->orWhere('nota', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('fecha_venta')
            ->orderByDesc('id')
            ->get();

        $totalVenta = (float) $ventas->sum(function (Venta $venta) {
            return (float) $venta->cantidad * (float) $venta->precio_venta_unitario;
        });

        $totalCosto = (float) $ventas->sum(function (Venta $venta) {
            return (float) $venta->cantidad * (float) $venta->precio_inventario_unitario;
        });

        $totales = [
            'piezas' => (int) $ventas->sum('cantidad'),
            'venta' => $totalVenta,
            'costo' => $totalCosto,
            'utilidad' => $totalVenta - $totalCosto,
        ];

        return view('ventas.index', compact('ventas', 'fecha', 'search', 'totales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $fecha = trim((string) $request->query('fecha', now()->toDateString()));
        $balatas = $this->balatasDisponibles();

        return view('ventas.create', compact('balatas', 'fecha'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateVenta($request);
        $cantidadVendida = (int) $validated['cantidad'];

        DB::transaction(function () use ($validated, $cantidadVendida) {
            /** @var \App\Models\Balata $balata */
            $balata = Balata::query()
                ->lockForUpdate()
                ->findOrFail($validated['balata_id']);

            if ((int) $balata->cantidad < $cantidadVendida) {
                throw ValidationException::withMessages([
                    'cantidad' => "No hay suficientes piezas disponibles para {$balata->codigo}. Existencia actual: {$balata->cantidad}.",
                ]);
            }

            $balata->decrement('cantidad', $cantidadVendida);

            Venta::create([
                'balata_id' => $balata->id,
                'codigo_balata' => $balata->codigo,
                'marca_balata' => $balata->marca,
                'cantidad' => $cantidadVendida,
                'precio_inventario_unitario' => $validated['precio_inventario_unitario'],
                'precio_venta_unitario' => $validated['precio_venta_unitario'],
                'fecha_venta' => $validated['fecha_venta'],
                'nota' => $validated['nota'] ?? null,
            ]);
        });

        return redirect()
            ->route('ventas.index', ['fecha' => $validated['fecha_venta']])
            ->with('success', 'Venta registrada y existencias actualizadas.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venta $venta)
    {
        $balatas = $this->balatasDisponibles();

        return view('ventas.edit', compact('venta', 'balatas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venta $venta)
    {
        $validated = $this->validateVenta($request);
        $cantidadNueva = (int) $validated['cantidad'];

        DB::transaction(function () use ($validated, $venta, $cantidadNueva) {
            /** @var \App\Models\Venta $ventaActual */
            $ventaActual = Venta::query()
                ->lockForUpdate()
                ->findOrFail($venta->id);

            $balataIds = collect([(int) $ventaActual->balata_id, (int) $validated['balata_id']])
                ->filter()
                ->unique()
                ->sort()
                ->values();

            $balatas = Balata::query()
                ->whereIn('id', $balataIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            /** @var \App\Models\Balata|null $balataOriginal */
            $balataOriginal = $balatas->get((int) $ventaActual->balata_id);
            /** @var \App\Models\Balata|null $balataNueva */
            $balataNueva = $balatas->get((int) $validated['balata_id']);

            if (! $balataNueva) {
                throw ValidationException::withMessages([
                    'balata_id' => 'La balata seleccionada no existe.',
                ]);
            }

            if ($balataOriginal && $balataOriginal->id === $balataNueva->id) {
                $existenciaDisponible = (int) $balataNueva->cantidad + (int) $ventaActual->cantidad;

                if ($cantidadNueva > $existenciaDisponible) {
                    throw ValidationException::withMessages([
                        'cantidad' => "No hay suficientes piezas disponibles para {$balataNueva->codigo}. Maximo permitido: {$existenciaDisponible}.",
                    ]);
                }

                $balataNueva->cantidad = $existenciaDisponible - $cantidadNueva;
                $balataNueva->save();
            } else {
                if ($balataOriginal) {
                    $balataOriginal->cantidad = (int) $balataOriginal->cantidad + (int) $ventaActual->cantidad;
                    $balataOriginal->save();
                }

                if ($cantidadNueva > (int) $balataNueva->cantidad) {
                    throw ValidationException::withMessages([
                        'cantidad' => "No hay suficientes piezas disponibles para {$balataNueva->codigo}. Existencia actual: {$balataNueva->cantidad}.",
                    ]);
                }

                $balataNueva->cantidad = (int) $balataNueva->cantidad - $cantidadNueva;
                $balataNueva->save();
            }

            $ventaActual->update([
                'balata_id' => $balataNueva->id,
                'codigo_balata' => $balataNueva->codigo,
                'marca_balata' => $balataNueva->marca,
                'cantidad' => $cantidadNueva,
                'precio_inventario_unitario' => $validated['precio_inventario_unitario'],
                'precio_venta_unitario' => $validated['precio_venta_unitario'],
                'fecha_venta' => $validated['fecha_venta'],
                'nota' => $validated['nota'] ?? null,
            ]);
        });

        return redirect()
            ->route('ventas.index', ['fecha' => $validated['fecha_venta']])
            ->with('success', 'Venta actualizada y existencias ajustadas.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Venta $venta)
    {
        $fecha = trim((string) $request->query('fecha', optional($venta->fecha_venta)->format('Y-m-d')));

        DB::transaction(function () use ($venta) {
            /** @var \App\Models\Venta $ventaActual */
            $ventaActual = Venta::query()
                ->lockForUpdate()
                ->findOrFail($venta->id);

            if ($ventaActual->balata_id) {
                /** @var \App\Models\Balata|null $balata */
                $balata = Balata::query()
                    ->lockForUpdate()
                    ->find($ventaActual->balata_id);

                if ($balata) {
                    $balata->cantidad = (int) $balata->cantidad + (int) $ventaActual->cantidad;
                    $balata->save();
                }
            }

            $ventaActual->delete();
        });

        return redirect()
            ->route('ventas.index', ['fecha' => $fecha])
            ->with('success', 'Venta eliminada y existencias restauradas.');
    }

    private function balatasDisponibles()
    {
        return Balata::query()
            ->orderBy('codigo')
            ->get([
                'id',
                'codigo',
                'marca',
                'cantidad',
                'precio_inventario',
                'precio_venta',
            ]);
    }

    private function validateVenta(Request $request): array
    {
        return $request->validate([
            'balata_id' => ['required', 'integer', 'exists:balatas,id'],
            'fecha_venta' => ['required', 'date'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'precio_inventario_unitario' => ['required', 'numeric', 'min:0'],
            'precio_venta_unitario' => ['required', 'numeric', 'min:0'],
            'nota' => ['nullable', 'string', 'max:1200'],
        ]);
    }
}
