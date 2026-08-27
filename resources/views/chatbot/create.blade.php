@extends('layouts.app')

@section('content')
<div class="page-heading">
    <h2>Crear Tema</h2>
</div>

    <div class="card">
        <div class="card-body">

            <form method="POST"
                  action="{{ route('chatbot.store') }}">

                @csrf

                @include('chatbot.partials.form')

                <div class="mt-4">
                    <button
                        type="submit"
                        class="btn btn-success">

                        Guardar
                    </button>

                    <a href="{{ route('chatbot.index') }}"
                       class="btn btn-secondary">

                        Cancelar
                    </a>
                </div>

            </form>

        </div>
    </div>
@endsection