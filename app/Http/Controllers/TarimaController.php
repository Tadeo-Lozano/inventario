<?php

namespace App\Http\Controllers;

use App\Models\Tarima;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TarimaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $tarimas = Tarima::query()
            ->with([
                'imagenes',
                'balatas' => function ($query) {
                    $query->select([
                        'id',
                        'tarima_id',
                        'codigo',
                        'marca',
                        'calidad',
                        'cantidad',
                        'precio_inventario',
                        'precio_venta',
                    ])->orderBy('codigo');
                },
            ])
            ->withCount('balatas')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('numero_identificacion', 'like', "%{$search}%")
                        ->orWhereHas('balatas', function ($balataQuery) use ($search) {
                            $balataQuery->where('codigo', 'like', "%{$search}%")
                                ->orWhere('vehiculos', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('numero_identificacion')
            ->get();

        return view('tarimas.index', compact('tarimas', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tarimas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_identificacion' => ['required', 'string', 'max:50', 'unique:tarimas,numero_identificacion'],
            'imagenes' => ['nullable', 'array'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $tarima = Tarima::create([
            'numero_identificacion' => trim($validated['numero_identificacion']),
        ]);

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $imagen) {
                $ruta = $imagen->store('tarimas', 'public');
                $tarima->imagenes()->create(['ruta' => $ruta]);
            }
        }

        return redirect()->route('tarimas.index')
            ->with('success', 'Tarima creada.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tarima $tarima)
    {
        $tarima->load('imagenes');

        return view('tarimas.edit', compact('tarima'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tarima $tarima)
    {
        $validated = $request->validate([
            'numero_identificacion' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tarimas', 'numero_identificacion')->ignore($tarima->id),
            ],
            'imagenes' => ['nullable', 'array'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'eliminar_imagenes' => ['nullable', 'array'],
            'eliminar_imagenes.*' => ['integer'],
        ]);

        if (! empty($validated['eliminar_imagenes'])) {
            $imagenes = $tarima->imagenes()
                ->whereIn('id', $validated['eliminar_imagenes'])
                ->get();

            foreach ($imagenes as $imagen) {
                Storage::disk('public')->delete($imagen->ruta);
                $imagen->delete();
            }
        }

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $imagen) {
                $ruta = $imagen->store('tarimas', 'public');
                $tarima->imagenes()->create(['ruta' => $ruta]);
            }
        }

        $tarima->numero_identificacion = trim($validated['numero_identificacion']);
        $tarima->save();

        return redirect()->route('tarimas.index')
            ->with('success', 'Tarima actualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tarima $tarima)
    {
        if ($tarima->balatas()->exists()) {
            return back()->with('error', 'No se puede eliminar una tarima con balatas asignadas.');
        }

        $tarima->load('imagenes');

        foreach ($tarima->imagenes as $imagen) {
            Storage::disk('public')->delete($imagen->ruta);
        }

        if ($tarima->foto) {
            Storage::disk('public')->delete($tarima->foto);
        }

        $tarima->delete();

        return redirect()->route('tarimas.index')
            ->with('success', 'Tarima eliminada.');
    }
}
