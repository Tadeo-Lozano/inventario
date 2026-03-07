@extends('layouts.app')

@section('title', 'Editar taller')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Editar taller: {{ $taller->nombre }}</h1>
        <a href="{{ route('talleres.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('talleres.update', $taller) }}">
                @csrf
                @method('PUT')
                @include('talleres._form', ['taller' => $taller])
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Actualizar taller</button>
                </div>
            </form>
        </div>
    </div>
@endsection
