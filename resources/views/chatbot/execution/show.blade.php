@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h3>Execution Logs</h3>

    <p>Conversation ID: {{ $conversation->id }}</p>

    <p>Total logs: {{ $logs->count() }}</p>

</div>
@endsection