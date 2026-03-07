@extends('layouts.app')

@section('title', 'Editar tarima')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Editar tarima: {{ $tarima->numero_identificacion }}</h1>
        <a href="{{ route('tarimas.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('tarimas.update', $tarima) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('tarimas._form', ['tarima' => $tarima])
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Actualizar tarima</button>
                </div>
            </form>
        </div>
    </div>
@endsection
