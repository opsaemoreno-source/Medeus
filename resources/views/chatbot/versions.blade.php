@extends('layouts.app')

@section('content')

<div class="page-heading">
    <h2>Historial: {{ $topic->name }}</h2>
    <a href="{{ route('chatbot.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

    <div class="card">

        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>

                @forelse($versions as $version)

                    <tr>

                        <td>
                            {{ $version->id }}
                        </td>

                        <td>
                            {{ $version->created_at }}
                        </td>

                        <td>

                            <form
                                method="POST"
                                action="{{ route('chatbot.restore-version', [$topic, $version]) }}">

                                @csrf

                                <button
                                    class="btn btn-sm btn-warning">

                                    Restaurar
                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="3">
                            Sin versiones.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>
          </div>
        </div>

    </div>
@endsection