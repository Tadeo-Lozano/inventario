@extends('layouts.app')

@section('title', 'Nuevo taller')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Registrar taller</h1>
        <a href="{{ route('talleres.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('talleres.store') }}">
                @csrf
                @include('talleres._form')
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Guardar taller</button>
                </div>
            </form>
        </div>
    </div>
@endsection
