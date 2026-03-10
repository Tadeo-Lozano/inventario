<?php

namespace App\Http\Controllers;

use App\Models\Balata;
use App\Models\Tarima;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BalataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $balatas = $this->inventarioQuery($search)->get();

        return view('balatas.index', compact('balatas', 'search'));
    }

    /**
     * Export inventory list to PDF.
     */
    public function exportPdf(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $balatas = $this->inventarioQuery($search)->get();
        $generatedAt = now();
        $totalPiezas = (int) $balatas->sum('cantidad');

        $pdf = Pdf::loadView('balatas.pdf', compact('balatas', 'search', 'generatedAt', 'totalPiezas'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('inventario-balatas-'.$generatedAt->format('Ymd-His').'.pdf');
    }

    /**
     * Display an inventory catalog summary.
     */
    public function catalogo()
    {
        $balatas = Balata::query()
            ->with('tarima')
            ->orderBy('codigo')
            ->get();

        return view('balatas.catalogo', compact('balatas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tarimas = Tarima::orderBy('numero_identificacion')->get();

        return view('balatas.create', compact('tarimas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $opcionesCalidad = ['Estandar', 'Premium', 'Ceramica'];
        $opcionesPosicion = ['Delantera', 'Trasera'];

        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:80', 'unique:balatas,codigo'],
            'marca' => ['required', 'string', 'max:80'],
            'calidad' => ['required', Rule::in($opcionesCalidad)],
            'posicion' => ['required', Rule::in($opcionesPosicion)],
            'vehiculos' => ['required', 'string'],
            'cantidad' => ['required', 'integer', 'min:0'],
            'precio_inventario' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'tarima_id' => ['required', 'exists:tarimas,id'],
            'imagenes' => ['nullable', 'array'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $balata = Balata::create([
            'codigo' => $validated['codigo'],
            'marca' => $validated['marca'],
            'calidad' => $validated['calidad'],
            'posicion' => $validated['posicion'],
            'vehiculos' => trim($validated['vehiculos']),
            'cantidad' => $validated['cantidad'],
            'precio_inventario' => $validated['precio_inventario'],
            'precio_venta' => $validated['precio_venta'],
            'tarima_id' => $validated['tarima_id'],
        ]);

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $imagen) {
                $ruta = $imagen->store('balatas', 'public');
                $balata->imagenes()->create(['ruta' => $ruta]);
            }
        }

        return redirect()->route('balatas.index')
            ->with('success', 'Balata creada correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Balata $balata)
    {
        $tarimas = Tarima::orderBy('numero_identificacion')->get();
        $balata->load('imagenes');

        return view('balatas.edit', compact('balata', 'tarimas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Balata $balata)
    {
        $opcionesCalidad = ['Estandar', 'Premium', 'Ceramica'];
        $opcionesPosicion = ['Delantera', 'Trasera'];

        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:80', Rule::unique('balatas', 'codigo')->ignore($balata->id)],
            'marca' => ['required', 'string', 'max:80'],
            'calidad' => ['required', Rule::in($opcionesCalidad)],
            'posicion' => ['required', Rule::in($opcionesPosicion)],
            'vehiculos' => ['required', 'string'],
            'cantidad' => ['required', 'integer', 'min:0'],
            'precio_inventario' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'tarima_id' => ['required', 'exists:tarimas,id'],
            'imagenes' => ['nullable', 'array'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'eliminar_imagenes' => ['nullable', 'array'],
            'eliminar_imagenes.*' => ['integer'],
        ]);

        $balata->update([
            'codigo' => $validated['codigo'],
            'marca' => $validated['marca'],
            'calidad' => $validated['calidad'],
            'posicion' => $validated['posicion'],
            'vehiculos' => trim($validated['vehiculos']),
            'cantidad' => $validated['cantidad'],
            'precio_inventario' => $validated['precio_inventario'],
            'precio_venta' => $validated['precio_venta'],
            'tarima_id' => $validated['tarima_id'],
        ]);

        if (! empty($validated['eliminar_imagenes'])) {
            $imagenes = $balata->imagenes()
                ->whereIn('id', $validated['eliminar_imagenes'])
                ->get();

            foreach ($imagenes as $imagen) {
                Storage::disk('public')->delete($imagen->ruta);
                $imagen->delete();
            }
        }

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $imagen) {
                $ruta = $imagen->store('balatas', 'public');
                $balata->imagenes()->create(['ruta' => $ruta]);
            }
        }

        return redirect()->route('balatas.index')
            ->with('success', 'Balata actualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Balata $balata)
    {
        $balata->load('imagenes');

        foreach ($balata->imagenes as $imagen) {
            Storage::disk('public')->delete($imagen->ruta);
        }

        $balata->delete();

        return redirect()->route('balatas.index')
            ->with('success', 'Balata eliminada.');
    }

    private function inventarioQuery(string $search): Builder
    {
        return Balata::query()
            ->with(['tarima', 'imagenes', 'talleres'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $subQuery) use ($search) {
                    $subQuery->where('codigo', 'like', "%{$search}%")
                        ->orWhere('marca', 'like', "%{$search}%")
                        ->orWhere('calidad', 'like', "%{$search}%")
                        ->orWhere('posicion', 'like', "%{$search}%")
                        ->orWhere('vehiculos', 'like', "%{$search}%")
                        ->orWhereHas('tarima', function (Builder $tarimaQuery) use ($search) {
                            $tarimaQuery->where('numero_identificacion', 'like', "%{$search}%");
                        })
                        ->orWhereHas('talleres', function (Builder $tallerQuery) use ($search) {
                            $tallerQuery->where('nombre', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('created_at');
    }
}
