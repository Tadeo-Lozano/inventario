@extends('layouts.app')

@section('title', 'Editar balata')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Editar balata: {{ $balata->codigo }}</h1>
        <a href="{{ route('balatas.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('balatas.update', $balata) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('balatas._form', ['balata' => $balata])
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Actualizar balata</button>
                </div>
            </form>
        </div>
    </div>
@endsection
