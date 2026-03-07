@extends('layouts.app')

@section('title', 'Nueva balata')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Capturar nueva balata</h1>
        <a href="{{ route('balatas.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
    </div>

    @if ($tarimas->isEmpty())
        <div class="alert alert-warning">
            Primero necesitas crear al menos una tarima para poder registrar balatas.
            <a href="{{ route('tarimas.create') }}" class="alert-link">Crear tarima</a>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('balatas.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('balatas._form')
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Guardar balata</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
