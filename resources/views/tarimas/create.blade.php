@extends('layouts.app')

@section('title', 'Nueva tarima')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Registrar tarima</h1>
        <a href="{{ route('tarimas.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('tarimas.store') }}" enctype="multipart/form-data">
                @csrf
                @include('tarimas._form')
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Guardar tarima</button>
                </div>
            </form>
        </div>
    </div>
@endsection
