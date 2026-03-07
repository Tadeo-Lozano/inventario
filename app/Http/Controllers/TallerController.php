<?php

namespace App\Http\Controllers;

use App\Models\Balata;
use App\Models\Taller;
use Illuminate\Http\Request;

class TallerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $talleres = Taller::query()
            ->with('balatas')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nombre', 'like', "%{$search}%")
                        ->orWhere('ubicacion', 'like', "%{$search}%")
                        ->orWhereHas('balatas', function ($balataQuery) use ($search) {
                            $balataQuery->where('codigo', 'like', "%{$search}%")
                                ->orWhere('calidad', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('nombre')
            ->get();

        return view('talleres.index', compact('talleres', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $balatas = Balata::orderBy('codigo')->get();

        return view('talleres.create', compact('balatas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'ubicacion' => ['required', 'string', 'max:255'],
            'balatas' => ['nullable', 'array'],
            'balatas.*' => ['integer', 'exists:balatas,id'],
        ]);

        $taller = Taller::create([
            'nombre' => trim($validated['nombre']),
            'ubicacion' => trim($validated['ubicacion']),
        ]);

        $taller->balatas()->sync($validated['balatas'] ?? []);

        return redirect()->route('talleres.index')
            ->with('success', 'Taller guardado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Taller $tallere)
    {
        $balatas = Balata::orderBy('codigo')->get();
        $tallere->load('balatas');

        return view('talleres.edit', [
            'taller' => $tallere,
            'balatas' => $balatas,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Taller $tallere)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'ubicacion' => ['required', 'string', 'max:255'],
            'balatas' => ['nullable', 'array'],
            'balatas.*' => ['integer', 'exists:balatas,id'],
        ]);

        $tallere->update([
            'nombre' => trim($validated['nombre']),
            'ubicacion' => trim($validated['ubicacion']),
        ]);

        $tallere->balatas()->sync($validated['balatas'] ?? []);

        return redirect()->route('talleres.index')
            ->with('success', 'Taller actualizado.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Taller $tallere)
    {
        $tallere->delete();

        return redirect()->route('talleres.index')
            ->with('success', 'Taller eliminado.');
    }
}
