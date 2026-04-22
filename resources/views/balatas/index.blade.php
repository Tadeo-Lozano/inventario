@extends('layouts.app')

@section('title', 'Balatas')

@section('content')
    @php
        $sortLabels = [
            'created_at' => 'Mas recientes',
            'codigo' => 'Codigo',
            'marca' => 'Marca',
            'calidad' => 'Calidad',
            'posicion' => 'Posicion',
            'vehiculos' => 'Vehiculos',
            'cantidad' => 'Cantidad',
            'precio_inventario' => 'Precio inventario',
            'precio_venta' => 'Precio venta',
            'tarima' => 'Tarima',
        ];

        $filterQuery = array_filter($filters, fn ($value) => ! ($value === '' || $value === null));

        $queryParams = [
            'sort' => $sort,
            'direction' => $direction,
        ];

        if ($search !== '') {
            $queryParams['search'] = $search;
        }

        if ($filterQuery !== []) {
            $queryParams['filters'] = $filterQuery;
        }

        $pdfQuery = $queryParams;
        $activeSortLabel = $sortLabels[$sort] ?? 'Mas recientes';
        $activeFilterCount = count($filterQuery);
        $totalPiezas = (int) $balatas->sum('cantidad');
        $stockValue = $balatas->reduce(
            fn (float $carry, $balata) => $carry + ((float) $balata->cantidad * (float) $balata->precio_inventario),
            0.0
        );

        $sortUrl = function (string $column) use ($search, $sort, $direction, $filterQuery) {
            $params = [
                'sort' => $column,
                'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc',
            ];

            if ($search !== '') {
                $params['search'] = $search;
            }

            if ($filterQuery !== []) {
                $params['filters'] = $filterQuery;
            }

            return route('balatas.index', $params);
        };

        $sortIndicatorLabel = function (string $column) use ($sort, $direction) {
            if ($sort !== $column) {
                return 'Sort';
            }

            return $direction === 'asc' ? 'Asc' : 'Desc';
        };

        $sortIndicatorClass = fn (string $column) => $sort === $column ? 'is-active' : 'is-idle';

        $qualityTone = function (?string $quality) {
            return match ($quality) {
                'Premium' => 'inventory-pill inventory-pill-premium',
                'Ceramica' => 'inventory-pill inventory-pill-ceramic',
                default => 'inventory-pill inventory-pill-standard',
            };
        };

        $positionTone = function (?string $position) {
            return $position === 'Trasera'
                ? 'inventory-pill inventory-pill-rear'
                : 'inventory-pill inventory-pill-front';
        };
    @endphp

    <style>
        .inventory-shell {
            --inventory-ink: #12263a;
            --inventory-ink-soft: #355070;
            --inventory-accent: #c8553d;
            --inventory-gold: #d6a85f;
            --inventory-surface: #ffffff;
            --inventory-surface-soft: #faf6f0;
            --inventory-surface-strong: #f2e8dc;
            --inventory-line: #e7dccf;
            --inventory-line-strong: #d6c6b5;
            --inventory-muted: #6f7c87;
            color: var(--inventory-ink);
        }

        .inventory-hero {
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, rgba(214, 168, 95, .36), transparent 32%),
                linear-gradient(135deg, #11263b 0%, #1d3b53 45%, #355070 100%);
            box-shadow: 0 24px 50px rgba(17, 38, 59, .16);
        }

        .inventory-hero::after {
            content: '';
            position: absolute;
            inset: auto -60px -80px auto;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .18), transparent 68%);
            pointer-events: none;
        }

        .inventory-hero .card-body {
            position: relative;
            z-index: 1;
        }

        .inventory-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .42rem .8rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            color: rgba(255, 255, 255, .88);
            font-size: .74rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .inventory-title {
            margin: 1rem 0 .65rem;
            color: #fff;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 1.02;
        }

        .inventory-lead {
            max-width: 700px;
            margin: 0;
            color: rgba(255, 255, 255, .8);
            font-size: 1rem;
        }

        .inventory-actions .btn {
            border-radius: 16px;
            padding: .95rem 1.1rem;
            font-weight: 600;
            border-width: 0;
            box-shadow: 0 16px 28px rgba(0, 0, 0, .14);
        }

        .inventory-actions .btn-danger {
            background: linear-gradient(135deg, #c8553d 0%, #a12d1f 100%);
        }

        .inventory-actions .btn-primary {
            background: linear-gradient(135deg, #f0c36d 0%, #d6a85f 100%);
            color: #182433;
        }

        .inventory-stat {
            height: 100%;
            padding: 1rem 1.1rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .12);
            color: #fff;
            backdrop-filter: blur(8px);
        }

        .inventory-stat-label {
            display: block;
            margin-bottom: .4rem;
            color: rgba(255, 255, 255, .72);
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .inventory-stat-value {
            display: block;
            font-size: 1.55rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .inventory-stat-note {
            display: block;
            margin-top: .2rem;
            color: rgba(255, 255, 255, .72);
            font-size: .82rem;
        }

        .inventory-panel {
            border: 1px solid var(--inventory-line);
            border-radius: 24px;
            background:
                linear-gradient(180deg, #fff 0%, var(--inventory-surface-soft) 100%);
            box-shadow: 0 20px 40px rgba(17, 38, 59, .06);
        }

        .inventory-panel-title {
            margin: 0;
            color: var(--inventory-ink);
            font-size: 1.15rem;
            font-weight: 700;
        }

        .inventory-panel-subtitle {
            margin-top: .25rem;
            color: var(--inventory-muted);
            font-size: .92rem;
        }

        .inventory-order-chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            align-self: start;
            padding: .55rem .8rem;
            border-radius: 999px;
            background: #fff;
            border: 1px solid var(--inventory-line);
            color: var(--inventory-ink-soft);
            font-size: .82rem;
            font-weight: 600;
            box-shadow: 0 10px 24px rgba(17, 38, 59, .05);
        }

        .inventory-label {
            display: block;
            margin-bottom: .45rem;
            color: var(--inventory-muted);
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .inventory-input,
        .inventory-select {
            min-height: 52px;
            border: 1px solid var(--inventory-line);
            border-radius: 16px;
            background: #fff;
            color: var(--inventory-ink);
            box-shadow: 0 8px 20px rgba(17, 38, 59, .04);
        }

        .inventory-input:focus,
        .inventory-select:focus {
            border-color: rgba(53, 80, 112, .35);
            box-shadow: 0 0 0 .25rem rgba(53, 80, 112, .12);
        }

        .inventory-btn {
            min-height: 52px;
            border-radius: 16px;
            font-weight: 600;
        }

        .inventory-btn-search {
            background: linear-gradient(135deg, #1d3b53 0%, #2f556c 100%);
            border: 0;
            color: #fff;
            box-shadow: 0 16px 24px rgba(17, 38, 59, .12);
        }

        .inventory-btn-search:hover,
        .inventory-btn-search:focus {
            color: #fff;
            background: linear-gradient(135deg, #173247 0%, #28495c 100%);
        }

        .inventory-btn-reset {
            border: 1px solid var(--inventory-line-strong);
            background: rgba(255, 255, 255, .88);
            color: var(--inventory-ink-soft);
        }

        .inventory-summary {
            display: flex;
            flex-wrap: wrap;
            gap: .6rem;
            margin-top: 1rem;
        }

        .inventory-summary-pill {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .55rem .8rem;
            border-radius: 999px;
            background: #fff;
            border: 1px solid var(--inventory-line);
            color: var(--inventory-ink-soft);
            font-size: .84rem;
            font-weight: 600;
        }

        .inventory-divider {
            height: 1px;
            margin: 1.35rem 0 1.2rem;
            background: linear-gradient(90deg, rgba(214, 198, 181, 0), rgba(214, 198, 181, .95), rgba(214, 198, 181, 0));
        }

        .inventory-subsection-title {
            margin: 0;
            color: var(--inventory-ink);
            font-size: .98rem;
            font-weight: 700;
        }

        .inventory-subsection-copy {
            margin-top: .2rem;
            color: var(--inventory-muted);
            font-size: .86rem;
        }

        .inventory-mobile-card {
            border: 1px solid rgba(214, 198, 181, .55);
            border-radius: 24px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .96) 0%, rgba(250, 246, 240, .96) 100%);
            box-shadow: 0 18px 30px rgba(17, 38, 59, .07);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .inventory-mobile-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 34px rgba(17, 38, 59, .1);
        }

        .inventory-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }

        .inventory-card-kicker {
            display: block;
            margin-bottom: .35rem;
            color: var(--inventory-muted);
            font-size: .78rem;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .inventory-card-code {
            display: inline-flex;
            align-items: center;
            padding: .45rem .75rem;
            border-radius: 999px;
            background: #fff;
            border: 1px solid var(--inventory-line);
            color: var(--inventory-ink);
            font-weight: 700;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .6);
        }

        .inventory-card-price {
            min-width: 130px;
            padding: .8rem .9rem;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(18, 38, 58, .96) 0%, rgba(53, 80, 112, .96) 100%);
            color: #fff;
            text-align: right;
            box-shadow: 0 16px 26px rgba(17, 38, 59, .16);
        }

        .inventory-card-price span {
            display: block;
            color: rgba(255, 255, 255, .72);
            font-size: .75rem;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .inventory-card-price strong {
            display: block;
            margin-top: .2rem;
            font-size: 1.05rem;
        }

        .inventory-badge-row {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
        }

        .inventory-pill {
            display: inline-flex;
            align-items: center;
            padding: .42rem .75rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .01em;
        }

        .inventory-pill-standard {
            background: #e9f2f9;
            color: #254663;
        }

        .inventory-pill-premium {
            background: #fef1c8;
            color: #7a4b00;
        }

        .inventory-pill-ceramic {
            background: #efe7ff;
            color: #5d3e9d;
        }

        .inventory-pill-front {
            background: #fde2dd;
            color: #9d3125;
        }

        .inventory-pill-rear {
            background: #dce9f8;
            color: #284964;
        }

        .inventory-mini-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }

        .inventory-mini-panel {
            padding: .85rem .9rem;
            border-radius: 18px;
            background: #fff;
            border: 1px solid var(--inventory-line);
        }

        .inventory-mini-panel.full {
            grid-column: 1 / -1;
        }

        .inventory-mini-label {
            display: block;
            margin-bottom: .25rem;
            color: var(--inventory-muted);
            font-size: .74rem;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .inventory-mini-value {
            color: var(--inventory-ink);
            font-weight: 600;
            word-break: break-word;
        }

        .inventory-image-row {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
        }

        .inventory-thumb-btn {
            padding: 0;
            border: 0;
            background: transparent;
            transition: transform .18s ease;
        }

        .inventory-thumb-btn:hover {
            transform: translateY(-2px);
        }

        .inventory-thumb {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 14px;
            border: 1px solid rgba(214, 198, 181, .75);
            box-shadow: 0 12px 18px rgba(17, 38, 59, .08);
        }

        .inventory-empty-copy {
            color: var(--inventory-muted);
            font-size: .92rem;
        }

        .inventory-taller-pill {
            display: inline-flex;
            align-items: center;
            padding: .38rem .7rem;
            border-radius: 999px;
            background: #fff;
            border: 1px solid var(--inventory-line);
            color: var(--inventory-ink-soft);
            font-size: .76rem;
            font-weight: 600;
        }

        .inventory-table-card {
            overflow: hidden;
            border: 1px solid var(--inventory-line);
            border-radius: 26px;
            background: #fff;
            box-shadow: 0 22px 42px rgba(17, 38, 59, .07);
        }

        .inventory-table-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.4rem;
            border-bottom: 1px solid var(--inventory-line);
            background:
                linear-gradient(180deg, rgba(250, 246, 240, .95) 0%, rgba(255, 255, 255, .92) 100%);
        }

        .inventory-table-topbar strong {
            display: block;
            color: var(--inventory-ink);
            font-size: .95rem;
        }

        .inventory-table-topbar span {
            color: var(--inventory-muted);
            font-size: .85rem;
        }

        .inventory-table {
            margin-bottom: 0;
            --bs-table-bg: transparent;
        }

        .inventory-table thead th {
            padding: 1rem 1rem .95rem;
            background: #f8f2e9;
            border-bottom: 1px solid var(--inventory-line);
            color: #5f6973;
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .inventory-table tbody td {
            padding: 1rem;
            border-color: rgba(231, 220, 207, .8);
            color: var(--inventory-ink);
            vertical-align: middle;
        }

        .inventory-table tbody tr {
            transition: background-color .18s ease;
        }

        .inventory-table tbody tr:nth-child(even) {
            background: rgba(250, 246, 240, .45);
        }

        .inventory-table tbody tr:hover {
            background: rgba(255, 250, 243, .95);
        }

        .inventory-sort-link {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            color: var(--inventory-ink-soft);
            text-decoration: none;
            font-weight: 700;
        }

        .inventory-sort-link:hover {
            color: var(--inventory-ink);
        }

        .inventory-sort-indicator {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 54px;
            padding: .28rem .55rem;
            border-radius: 999px;
            font-size: .66rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .inventory-sort-indicator.is-idle {
            background: #fff;
            border: 1px solid var(--inventory-line);
            color: #8a7965;
        }

        .inventory-sort-indicator.is-active {
            background: linear-gradient(135deg, #12263a 0%, #355070 100%);
            color: #fff;
            box-shadow: 0 10px 16px rgba(17, 38, 59, .16);
        }

        .inventory-code-cell {
            display: inline-flex;
            align-items: center;
            padding: .4rem .72rem;
            border-radius: 999px;
            background: #f7f2eb;
            border: 1px solid var(--inventory-line);
            color: var(--inventory-ink);
            font-weight: 700;
        }

        .inventory-brand-cell {
            font-weight: 600;
            color: #2e4358;
        }

        .inventory-vehicles-cell {
            min-width: 270px;
            color: #4e5d69;
            white-space: pre-line;
        }

        .inventory-money-cell {
            font-weight: 700;
            color: #173247;
        }

        .inventory-table .btn {
            border-radius: 12px;
            font-weight: 600;
        }

        .inventory-empty-state {
            padding: 3.25rem 1.5rem;
            background:
                radial-gradient(circle at top, rgba(214, 168, 95, .12), transparent 28%),
                linear-gradient(180deg, rgba(250, 246, 240, .78) 0%, rgba(255, 255, 255, .95) 100%);
        }

        @media (max-width: 991.98px) {
            .inventory-actions .btn {
                box-shadow: none;
            }

            .inventory-order-chip {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 767.98px) {
            .inventory-title {
                font-size: 1.9rem;
            }

            .inventory-mini-grid {
                grid-template-columns: 1fr;
            }

            .inventory-card-price {
                min-width: 118px;
            }

            .inventory-thumb {
                width: 66px;
                height: 66px;
            }
        }
    </style>

    <div class="inventory-shell">
        <div class="card inventory-hero mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <span class="inventory-eyebrow">Inventario activo</span>
                        <h1 class="inventory-title">Inventario de balatas</h1>
                        
                    </div>
                    <div class="col-lg-4">
                        <div class="inventory-actions d-grid gap-2">
                            <a href="{{ route('balatas.export.pdf', $pdfQuery) }}" class="btn btn-danger btn-mobile-block">
                                Exportar PDF
                            </a>
                            <a href="{{ route('balatas.create') }}" class="btn btn-primary btn-mobile-block">
                                Nueva balata
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-sm-4">
                        <div class="inventory-stat">
                            <span class="inventory-stat-label">Registros</span>
                            <strong class="inventory-stat-value">{{ number_format($balatas->count()) }}</strong>
                            <span class="inventory-stat-note">balatas visibles en esta consulta</span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="inventory-stat">
                            <span class="inventory-stat-label">Piezas</span>
                            <strong class="inventory-stat-value">{{ number_format($totalPiezas) }}</strong>
                            <span class="inventory-stat-note">existencia total en el resultado</span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="inventory-stat">
                            <span class="inventory-stat-label">Valor inventario</span>
                            <strong class="inventory-stat-value">${{ number_format($stockValue, 2) }}</strong>
                            <span class="inventory-stat-note">estimado con precio de inventario</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card inventory-panel mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                    <div>
                        <h2 class="inventory-panel-title">Buscar y ordenar</h2>
                        <div class="inventory-panel-subtitle">
                            La busqueda, el orden y los filtros por columna se conservan al exportar o navegar por la tabla.
                        </div>
                    </div>
                    <div class="inventory-order-chip">
                        <span>Orden actual</span>
                        <strong>{{ $activeSortLabel }}</strong>
                        <span>{{ $direction === 'asc' ? 'ascendente' : 'descendente' }}</span>
                    </div>
                </div>

                <form method="GET" action="{{ route('balatas.index') }}" class="search-form">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-5">
                            <label for="inventorySearch" class="inventory-label">Busqueda</label>
                            <input
                                id="inventorySearch"
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                class="form-control inventory-input"
                                placeholder="Buscar por codigo, marca, calidad, posicion, vehiculo, tarima o taller"
                            >
                        </div>
                        <div class="col-6 col-md-4 col-xl-2">
                            <label for="inventorySort" class="inventory-label">Ordenar por</label>
                            <select id="inventorySort" name="sort" class="form-select inventory-select">
                                @foreach ($sortLabels as $sortKey => $sortLabel)
                                    <option value="{{ $sortKey }}" @selected($sort === $sortKey)>{{ $sortLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-xl-2">
                            <label for="inventoryDirection" class="inventory-label">Direccion</label>
                            <select id="inventoryDirection" name="direction" class="form-select inventory-select">
                                <option value="asc" @selected($direction === 'asc')>Ascendente</option>
                                <option value="desc" @selected($direction === 'desc')>Descendente</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2 col-xl-1">
                            <button type="submit" class="btn inventory-btn inventory-btn-search w-100">Aplicar</button>
                        </div>
                        <div class="col-6 col-md-2 col-xl-2">
                            <a href="{{ route('balatas.index') }}" class="btn inventory-btn inventory-btn-reset w-100">Limpiar</a>
                        </div>
                    </div>

                    <div class="inventory-divider"></div>

                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="inventory-subsection-title">Filtros por columna</h3>
                            <div class="inventory-subsection-copy">
                                Combina campos especificos para acotar el inventario por codigo, marca, calidad, cantidades, precios y relaciones.
                            </div>
                        </div>
                        <div class="inventory-summary-pill">
                            {{ $activeFilterCount }} filtro(s) activo(s)
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-6 col-md-4 col-xl-2">
                            <label for="filterCodigo" class="inventory-label">Codigo</label>
                            <input
                                id="filterCodigo"
                                type="text"
                                name="filters[codigo]"
                                value="{{ $filters['codigo'] }}"
                                class="form-control inventory-input"
                                placeholder="Ej. BAL-001"
                            >
                        </div>
                        <div class="col-6 col-md-4 col-xl-2">
                            <label for="filterMarca" class="inventory-label">Marca</label>
                            <input
                                id="filterMarca"
                                type="text"
                                name="filters[marca]"
                                value="{{ $filters['marca'] }}"
                                class="form-control inventory-input"
                                placeholder="Ej. Brembo"
                            >
                        </div>
                        <div class="col-6 col-md-4 col-xl-2">
                            <label for="filterCalidad" class="inventory-label">Calidad</label>
                            <select id="filterCalidad" name="filters[calidad]" class="form-select inventory-select">
                                <option value="">Todas</option>
                                @foreach ($qualityOptions as $qualityOption)
                                    <option value="{{ $qualityOption }}" @selected($filters['calidad'] === $qualityOption)>{{ $qualityOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-xl-2">
                            <label for="filterPosicion" class="inventory-label">Posicion</label>
                            <select id="filterPosicion" name="filters[posicion]" class="form-select inventory-select">
                                <option value="">Todas</option>
                                @foreach ($positionOptions as $positionOption)
                                    <option value="{{ $positionOption }}" @selected($filters['posicion'] === $positionOption)>{{ $positionOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-xl-2">
                            <label for="filterTarima" class="inventory-label">Tarima</label>
                            <input
                                id="filterTarima"
                                type="text"
                                name="filters[tarima]"
                                value="{{ $filters['tarima'] }}"
                                class="form-control inventory-input"
                                placeholder="Ej. T-101"
                            >
                        </div>
                        <div class="col-6 col-md-4 col-xl-2">
                            <label for="filterImagenes" class="inventory-label">Imagenes</label>
                            <select id="filterImagenes" name="filters[imagenes]" class="form-select inventory-select">
                                <option value="">Todas</option>
                                <option value="with" @selected($filters['imagenes'] === 'with')>Con imagenes</option>
                                <option value="without" @selected($filters['imagenes'] === 'without')>Sin imagenes</option>
                            </select>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label for="filterVehiculos" class="inventory-label">Vehiculos</label>
                            <input
                                id="filterVehiculos"
                                type="text"
                                name="filters[vehiculos]"
                                value="{{ $filters['vehiculos'] }}"
                                class="form-control inventory-input"
                                placeholder="Modelo, linea o anotacion del vehiculo"
                            >
                        </div>
                        <div class="col-12 col-lg-6">
                            <label for="filterTaller" class="inventory-label">Talleres interesados</label>
                            <input
                                id="filterTaller"
                                type="text"
                                name="filters[taller]"
                                value="{{ $filters['taller'] }}"
                                class="form-control inventory-input"
                                placeholder="Nombre del taller"
                            >
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="filterCantidadMin" class="inventory-label">Cantidad min.</label>
                            <input
                                id="filterCantidadMin"
                                type="number"
                                min="0"
                                step="1"
                                name="filters[cantidad_min]"
                                value="{{ $filters['cantidad_min'] }}"
                                class="form-control inventory-input"
                                placeholder="0"
                            >
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="filterCantidadMax" class="inventory-label">Cantidad max.</label>
                            <input
                                id="filterCantidadMax"
                                type="number"
                                min="0"
                                step="1"
                                name="filters[cantidad_max]"
                                value="{{ $filters['cantidad_max'] }}"
                                class="form-control inventory-input"
                                placeholder="999"
                            >
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="filterPrecioInventarioMin" class="inventory-label">P. inventario min.</label>
                            <input
                                id="filterPrecioInventarioMin"
                                type="number"
                                min="0"
                                step="0.01"
                                name="filters[precio_inventario_min]"
                                value="{{ $filters['precio_inventario_min'] }}"
                                class="form-control inventory-input"
                                placeholder="0.00"
                            >
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="filterPrecioInventarioMax" class="inventory-label">P. inventario max.</label>
                            <input
                                id="filterPrecioInventarioMax"
                                type="number"
                                min="0"
                                step="0.01"
                                name="filters[precio_inventario_max]"
                                value="{{ $filters['precio_inventario_max'] }}"
                                class="form-control inventory-input"
                                placeholder="9999.00"
                            >
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="filterPrecioVentaMin" class="inventory-label">P. venta min.</label>
                            <input
                                id="filterPrecioVentaMin"
                                type="number"
                                min="0"
                                step="0.01"
                                name="filters[precio_venta_min]"
                                value="{{ $filters['precio_venta_min'] }}"
                                class="form-control inventory-input"
                                placeholder="0.00"
                            >
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="filterPrecioVentaMax" class="inventory-label">P. venta max.</label>
                            <input
                                id="filterPrecioVentaMax"
                                type="number"
                                min="0"
                                step="0.01"
                                name="filters[precio_venta_max]"
                                value="{{ $filters['precio_venta_max'] }}"
                                class="form-control inventory-input"
                                placeholder="9999.00"
                            >
                        </div>
                    </div>
                </form>

                <div class="inventory-summary">
                    <span class="inventory-summary-pill">{{ $balatas->count() }} resultado(s)</span>
                    <span class="inventory-summary-pill">{{ $activeSortLabel }}</span>
                    <span class="inventory-summary-pill">{{ $direction === 'asc' ? 'Ascendente' : 'Descendente' }}</span>
                    <span class="inventory-summary-pill">{{ $activeFilterCount }} filtro(s)</span>
                </div>
            </div>
        </div>

        <div class="d-md-none">
            @forelse ($balatas as $balata)
                @php
                    $galeriaBalata = $balata->imagenes->map(fn ($imagen) => route('media.show', ['path' => $imagen->ruta]))->values();
                @endphp

                <div class="inventory-mobile-card p-3 mb-3">
                    <div class="inventory-card-top">
                        <div>
                            <span class="inventory-card-kicker">Codigo</span>
                            <div class="inventory-card-code">{{ $balata->codigo }}</div>
                        </div>
                        <div class="inventory-card-price">
                            <span>Precio venta</span>
                            <strong>${{ number_format((float) $balata->precio_venta, 2) }}</strong>
                        </div>
                    </div>

                    <div class="inventory-badge-row mt-3">
                        <span class="inventory-taller-pill">{{ $balata->marca }}</span>
                        <span class="{{ $qualityTone($balata->calidad) }}">{{ $balata->calidad }}</span>
                        <span class="{{ $positionTone($balata->posicion) }}">{{ $balata->posicion }}</span>
                    </div>

                    <div class="inventory-mini-grid mt-3">
                        <div class="inventory-mini-panel">
                            <span class="inventory-mini-label">Cantidad</span>
                            <div class="inventory-mini-value">{{ number_format($balata->cantidad) }}</div>
                        </div>
                        <div class="inventory-mini-panel">
                            <span class="inventory-mini-label">Tarima</span>
                            <div class="inventory-mini-value">{{ $balata->tarima?->numero_identificacion ?? 'N/A' }}</div>
                        </div>
                        <div class="inventory-mini-panel full">
                            <span class="inventory-mini-label">Precio inventario</span>
                            <div class="inventory-mini-value">${{ number_format((float) $balata->precio_inventario, 2) }}</div>
                        </div>
                        <div class="inventory-mini-panel full">
                            <span class="inventory-mini-label">Vehiculos</span>
                            <div class="inventory-mini-value">{!! nl2br(e($balata->vehiculos)) !!}</div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <span class="inventory-mini-label">Imagenes</span>
                        @if ($galeriaBalata->isEmpty())
                            <div class="inventory-empty-copy">Sin imagen</div>
                        @else
                            <div class="inventory-image-row">
                                @foreach ($galeriaBalata as $url)
                                    <button
                                        type="button"
                                        class="inventory-thumb-btn"
                                        data-gallery-images='@json($galeriaBalata)'
                                        data-gallery-start="{{ $loop->index }}"
                                    >
                                        <img src="{{ $url }}" alt="Imagen de balata" class="inventory-thumb">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="mt-3">
                        <span class="inventory-mini-label">Talleres interesados</span>
                        @if ($balata->talleres->isEmpty())
                            <div class="inventory-empty-copy">Ninguno</div>
                        @else
                            <div class="inventory-badge-row">
                                @foreach ($balata->talleres as $taller)
                                    <span class="inventory-taller-pill">{{ $taller->nombre }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <a href="{{ route('balatas.edit', $balata) }}" class="btn btn-sm btn-outline-primary flex-fill">Editar</a>
                        <form method="POST" action="{{ route('balatas.destroy', $balata) }}" class="flex-fill" onsubmit="return confirm('Eliminar esta balata?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">Eliminar</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="card inventory-panel">
                    <div class="card-body text-center py-5">
                        <div class="inventory-panel-title">No hay balatas registradas</div>
                        <div class="inventory-panel-subtitle">Prueba ajustando la busqueda o registra una nueva balata.</div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="inventory-table-card d-none d-md-block">
            <div class="inventory-table-topbar">
                <div>
                    <strong>Vista de escritorio</strong>
                    <span>Haz clic en un encabezado para alternar el orden rapidamente.</span>
                </div>
                <div class="inventory-order-chip">
                    <span>{{ $activeSortLabel }}</span>
                    <strong>{{ $direction === 'asc' ? 'Ascendente' : 'Descendente' }}</strong>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table inventory-table align-middle">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ $sortUrl('codigo') }}" class="inventory-sort-link">
                                    <span>Codigo</span>
                                    <span class="inventory-sort-indicator {{ $sortIndicatorClass('codigo') }}">{{ $sortIndicatorLabel('codigo') }}</span>
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('marca') }}" class="inventory-sort-link">
                                    <span>Marca</span>
                                    <span class="inventory-sort-indicator {{ $sortIndicatorClass('marca') }}">{{ $sortIndicatorLabel('marca') }}</span>
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('calidad') }}" class="inventory-sort-link">
                                    <span>Calidad</span>
                                    <span class="inventory-sort-indicator {{ $sortIndicatorClass('calidad') }}">{{ $sortIndicatorLabel('calidad') }}</span>
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('posicion') }}" class="inventory-sort-link">
                                    <span>Posicion</span>
                                    <span class="inventory-sort-indicator {{ $sortIndicatorClass('posicion') }}">{{ $sortIndicatorLabel('posicion') }}</span>
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('vehiculos') }}" class="inventory-sort-link">
                                    <span>Vehiculos</span>
                                    <span class="inventory-sort-indicator {{ $sortIndicatorClass('vehiculos') }}">{{ $sortIndicatorLabel('vehiculos') }}</span>
                                </a>
                            </th>
                            <th class="text-end">
                                <a href="{{ $sortUrl('cantidad') }}" class="inventory-sort-link justify-content-end">
                                    <span>Cantidad</span>
                                    <span class="inventory-sort-indicator {{ $sortIndicatorClass('cantidad') }}">{{ $sortIndicatorLabel('cantidad') }}</span>
                                </a>
                            </th>
                            <th class="text-end">
                                <a href="{{ $sortUrl('precio_inventario') }}" class="inventory-sort-link justify-content-end">
                                    <span>P. inventario</span>
                                    <span class="inventory-sort-indicator {{ $sortIndicatorClass('precio_inventario') }}">{{ $sortIndicatorLabel('precio_inventario') }}</span>
                                </a>
                            </th>
                            <th class="text-end">
                                <a href="{{ $sortUrl('precio_venta') }}" class="inventory-sort-link justify-content-end">
                                    <span>P. venta</span>
                                    <span class="inventory-sort-indicator {{ $sortIndicatorClass('precio_venta') }}">{{ $sortIndicatorLabel('precio_venta') }}</span>
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('tarima') }}" class="inventory-sort-link">
                                    <span>Tarima</span>
                                    <span class="inventory-sort-indicator {{ $sortIndicatorClass('tarima') }}">{{ $sortIndicatorLabel('tarima') }}</span>
                                </a>
                            </th>
                            <th>Imagenes</th>
                            <th>Talleres</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($balatas as $balata)
                            @php
                                $galeriaBalata = $balata->imagenes->map(fn ($imagen) => route('media.show', ['path' => $imagen->ruta]))->values();
                            @endphp

                            <tr>
                                <td><span class="inventory-code-cell">{{ $balata->codigo }}</span></td>
                                <td class="inventory-brand-cell">{{ $balata->marca }}</td>
                                <td><span class="{{ $qualityTone($balata->calidad) }}">{{ $balata->calidad }}</span></td>
                                <td><span class="{{ $positionTone($balata->posicion) }}">{{ $balata->posicion }}</span></td>
                                <td class="inventory-vehicles-cell">{!! nl2br(e($balata->vehiculos)) !!}</td>
                                <td class="text-end fw-semibold">{{ number_format($balata->cantidad) }}</td>
                                <td class="text-end inventory-money-cell">${{ number_format((float) $balata->precio_inventario, 2) }}</td>
                                <td class="text-end inventory-money-cell">${{ number_format((float) $balata->precio_venta, 2) }}</td>
                                <td><span class="inventory-taller-pill">{{ $balata->tarima?->numero_identificacion ?? 'N/A' }}</span></td>
                                <td>
                                    @if ($galeriaBalata->isEmpty())
                                        <span class="inventory-empty-copy">Sin imagen</span>
                                    @else
                                        <div class="inventory-image-row">
                                            @foreach ($galeriaBalata as $url)
                                                <button
                                                    type="button"
                                                    class="inventory-thumb-btn"
                                                    data-gallery-images='@json($galeriaBalata)'
                                                    data-gallery-start="{{ $loop->index }}"
                                                >
                                                    <img src="{{ $url }}" alt="Imagen de balata" class="inventory-thumb">
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($balata->talleres->isEmpty())
                                        <span class="inventory-empty-copy">Ninguno</span>
                                    @else
                                        <div class="inventory-badge-row">
                                            @foreach ($balata->talleres as $taller)
                                                <span class="inventory-taller-pill">{{ $taller->nombre }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('balatas.edit', $balata) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form method="POST" action="{{ route('balatas.destroy', $balata) }}" class="d-inline" onsubmit="return confirm('Eliminar esta balata?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="inventory-empty-state text-center">
                                    <div class="inventory-panel-title">No hay balatas registradas</div>
                                    <div class="inventory-panel-subtitle">Prueba ajustando la busqueda o crea un nuevo registro.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
