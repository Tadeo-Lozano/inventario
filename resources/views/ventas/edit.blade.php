@extends('layouts.app')

@section('title', 'Editar venta')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Editar venta: {{ $venta->codigo_balata }}</h1>
        <a href="{{ route('ventas.index', ['fecha' => optional($venta->fecha_venta)->format('Y-m-d')]) }}" class="btn btn-outline-secondary">Volver al listado</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('ventas.update', $venta) }}">
                @csrf
                @method('PUT')
                @include('ventas._form', ['venta' => $venta, 'fecha' => optional($venta->fecha_venta)->format('Y-m-d')])
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Actualizar venta</button>
                </div>
            </form>
        </div>
    </div>
@endsection
