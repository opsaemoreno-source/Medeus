@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h3>
        Historial:
        {{ $topic->name }}
    </h3>

    <div class="card mt-3">

        <div class="card-body">

            <table class="table">

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