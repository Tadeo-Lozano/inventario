<?php

namespace App\Http\Controllers;

use App\Models\Balata;
use App\Models\Tarima;
use App\Support\MediaStorage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BalataController extends Controller
{
    private const DEFAULT_SORT = 'created_at';

    private const DEFAULT_DIRECTION = 'desc';

    private const QUALITY_OPTIONS = ['Estandar', 'Premium', 'Ceramica'];

    private const POSITION_OPTIONS = ['Delantera', 'Trasera'];

    private const IMAGE_FILTER_OPTIONS = ['with', 'without'];

    private const SORTABLE_COLUMNS = [
        'created_at' => 'balatas.created_at',
        'codigo' => 'balatas.codigo',
        'marca' => 'balatas.marca',
        'calidad' => 'balatas.calidad',
        'posicion' => 'balatas.posicion',
        'vehiculos' => 'balatas.vehiculos',
        'cantidad' => 'balatas.cantidad',
        'precio_inventario' => 'balatas.precio_inventario',
        'precio_venta' => 'balatas.precio_venta',
        'tarima' => 'tarimas.numero_identificacion',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $sort = $this->resolveSort((string) $request->query('sort', self::DEFAULT_SORT));
        $direction = $this->resolveDirection((string) $request->query('direction', ''), $sort);
        $filters = $this->resolveFilters($request->query('filters', []));

        $balatas = $this->inventarioQuery($search, $sort, $direction, $filters)->get();

        $qualityOptions = self::QUALITY_OPTIONS;
        $positionOptions = self::POSITION_OPTIONS;

        return view('balatas.index', compact(
            'balatas',
            'search',
            'sort',
            'direction',
            'filters',
            'qualityOptions',
            'positionOptions'
        ));
    }

    /**
     * Export inventory list to PDF.
     */
    public function exportPdf(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $sort = $this->resolveSort((string) $request->query('sort', self::DEFAULT_SORT));
        $direction = $this->resolveDirection((string) $request->query('direction', ''), $sort);
        $filters = $this->resolveFilters($request->query('filters', []));
        $balatas = $this->inventarioQuery($search, $sort, $direction, $filters)->get();
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
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:80', 'unique:balatas,codigo'],
            'marca' => ['required', 'string', 'max:80'],
            'calidad' => ['required', Rule::in(self::QUALITY_OPTIONS)],
            'posicion' => ['required', Rule::in(self::POSITION_OPTIONS)],
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
                $ruta = $imagen->store('balatas', ['disk' => MediaStorage::diskName()]);
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
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:80', Rule::unique('balatas', 'codigo')->ignore($balata->id)],
            'marca' => ['required', 'string', 'max:80'],
            'calidad' => ['required', Rule::in(self::QUALITY_OPTIONS)],
            'posicion' => ['required', Rule::in(self::POSITION_OPTIONS)],
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
                MediaStorage::disk()->delete($imagen->ruta);
                $imagen->delete();
            }
        }

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $imagen) {
                $ruta = $imagen->store('balatas', ['disk' => MediaStorage::diskName()]);
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
            MediaStorage::disk()->delete($imagen->ruta);
        }

        $balata->delete();

        return redirect()->route('balatas.index')
            ->with('success', 'Balata eliminada.');
    }

    private function inventarioQuery(string $search, string $sort, string $direction, array $filters): Builder
    {
        $query = Balata::query()
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
            });

        $query = $this->applyFilters($query, $filters);

        return $this->applySort($query, $sort, $direction);
    }

    private function resolveFilters(mixed $filters): array
    {
        $filters = is_array($filters) ? $filters : [];

        $calidad = $this->normalizeStringFilter($filters['calidad'] ?? '');
        $posicion = $this->normalizeStringFilter($filters['posicion'] ?? '');
        $imagenes = $this->normalizeStringFilter($filters['imagenes'] ?? '');

        return [
            'codigo' => $this->normalizeStringFilter($filters['codigo'] ?? ''),
            'marca' => $this->normalizeStringFilter($filters['marca'] ?? ''),
            'calidad' => in_array($calidad, self::QUALITY_OPTIONS, true) ? $calidad : '',
            'posicion' => in_array($posicion, self::POSITION_OPTIONS, true) ? $posicion : '',
            'vehiculos' => $this->normalizeStringFilter($filters['vehiculos'] ?? ''),
            'cantidad_min' => $this->normalizeIntegerFilter($filters['cantidad_min'] ?? null),
            'cantidad_max' => $this->normalizeIntegerFilter($filters['cantidad_max'] ?? null),
            'precio_inventario_min' => $this->normalizeDecimalFilter($filters['precio_inventario_min'] ?? null),
            'precio_inventario_max' => $this->normalizeDecimalFilter($filters['precio_inventario_max'] ?? null),
            'precio_venta_min' => $this->normalizeDecimalFilter($filters['precio_venta_min'] ?? null),
            'precio_venta_max' => $this->normalizeDecimalFilter($filters['precio_venta_max'] ?? null),
            'tarima' => $this->normalizeStringFilter($filters['tarima'] ?? ''),
            'taller' => $this->normalizeStringFilter($filters['taller'] ?? ''),
            'imagenes' => in_array($imagenes, self::IMAGE_FILTER_OPTIONS, true) ? $imagenes : '',
        ];
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['codigo'] !== '', function (Builder $query) use ($filters) {
                $query->where('balatas.codigo', 'like', '%'.$filters['codigo'].'%');
            })
            ->when($filters['marca'] !== '', function (Builder $query) use ($filters) {
                $query->where('balatas.marca', 'like', '%'.$filters['marca'].'%');
            })
            ->when($filters['calidad'] !== '', function (Builder $query) use ($filters) {
                $query->where('balatas.calidad', $filters['calidad']);
            })
            ->when($filters['posicion'] !== '', function (Builder $query) use ($filters) {
                $query->where('balatas.posicion', $filters['posicion']);
            })
            ->when($filters['vehiculos'] !== '', function (Builder $query) use ($filters) {
                $query->where('balatas.vehiculos', 'like', '%'.$filters['vehiculos'].'%');
            })
            ->when($filters['cantidad_min'] !== null, function (Builder $query) use ($filters) {
                $query->where('balatas.cantidad', '>=', $filters['cantidad_min']);
            })
            ->when($filters['cantidad_max'] !== null, function (Builder $query) use ($filters) {
                $query->where('balatas.cantidad', '<=', $filters['cantidad_max']);
            })
            ->when($filters['precio_inventario_min'] !== null, function (Builder $query) use ($filters) {
                $query->where('balatas.precio_inventario', '>=', $filters['precio_inventario_min']);
            })
            ->when($filters['precio_inventario_max'] !== null, function (Builder $query) use ($filters) {
                $query->where('balatas.precio_inventario', '<=', $filters['precio_inventario_max']);
            })
            ->when($filters['precio_venta_min'] !== null, function (Builder $query) use ($filters) {
                $query->where('balatas.precio_venta', '>=', $filters['precio_venta_min']);
            })
            ->when($filters['precio_venta_max'] !== null, function (Builder $query) use ($filters) {
                $query->where('balatas.precio_venta', '<=', $filters['precio_venta_max']);
            })
            ->when($filters['tarima'] !== '', function (Builder $query) use ($filters) {
                $query->whereHas('tarima', function (Builder $tarimaQuery) use ($filters) {
                    $tarimaQuery->where('numero_identificacion', 'like', '%'.$filters['tarima'].'%');
                });
            })
            ->when($filters['taller'] !== '', function (Builder $query) use ($filters) {
                $query->whereHas('talleres', function (Builder $tallerQuery) use ($filters) {
                    $tallerQuery->where('nombre', 'like', '%'.$filters['taller'].'%');
                });
            })
            ->when($filters['imagenes'] === 'with', function (Builder $query) {
                $query->has('imagenes');
            })
            ->when($filters['imagenes'] === 'without', function (Builder $query) {
                $query->doesntHave('imagenes');
            });
    }

    private function resolveSort(string $sort): string
    {
        return array_key_exists($sort, self::SORTABLE_COLUMNS)
            ? $sort
            : self::DEFAULT_SORT;
    }

    private function resolveDirection(string $direction, string $sort): string
    {
        $direction = strtolower($direction);

        if (in_array($direction, ['asc', 'desc'], true)) {
            return $direction;
        }

        return $sort === self::DEFAULT_SORT ? self::DEFAULT_DIRECTION : 'asc';
    }

    private function normalizeStringFilter(mixed $value): string
    {
        return trim((string) $value);
    }

    private function normalizeIntegerFilter(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_INT) !== false
            ? (int) $value
            : null;
    }

    private function normalizeDecimalFilter(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value)
            ? (float) $value
            : null;
    }

    private function applySort(Builder $query, string $sort, string $direction): Builder
    {
        if ($sort === 'tarima') {
            $query->leftJoin('tarimas', 'tarimas.id', '=', 'balatas.tarima_id')
                ->select('balatas.*');
        }

        $query->orderBy(self::SORTABLE_COLUMNS[$sort], $direction);

        if ($sort !== 'codigo') {
            $query->orderBy('balatas.codigo');
        }

        return $query;
    }
}
