@extends('layouts.app')

@section('content')
<div class="page-heading">
    <h2>Perfil de Usuario</h2>
</div>
<div class="row g-4">
        <div class="col-lg-6">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="col-lg-6">
            @include('profile.partials.update-password-form')
        </div>

        <!--<div class="col-12">
            @include('profile.partials.delete-user-form')
        </div>-->
</div>
@endsection
