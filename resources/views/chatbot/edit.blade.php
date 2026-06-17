@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="card shadow-sm">

        <div class="card-header">
            <h4>Editar Tema</h4>
        </div>

        <div class="card-body">

            <form method="POST"
                  action="{{ route('chatbot.update', $topic) }}">

                @csrf
                @method('PUT')

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

</div>
@endsection