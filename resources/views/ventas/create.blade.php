@extends('layouts.app')

@section('title', 'Registrar venta')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Registrar venta / baja</h1>
        <a href="{{ route('ventas.index', ['fecha' => $fecha]) }}" class="btn btn-outline-secondary">Volver al listado</a>
    </div>

    @if ($balatas->isEmpty())
        <div class="alert alert-warning">
            No hay balatas registradas para vender.
            <a href="{{ route('balatas.create') }}" class="alert-link">Capturar balata</a>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('ventas.store') }}">
                    @csrf
                    @include('ventas._form', ['fecha' => $fecha])
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Guardar venta</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
