@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">

        {{-- SIDEBAR IZQUIERDO --}}
        <div class="col-md-3 col-lg-2 bg-light border-end" style="min-height: 100vh;">
            <h5 class="mt-4 mb-3">Estadísticas</h5>
            <div class="list-group">

                <button class="list-group-item list-group-item-action" id="btnEncuestas">
                    Encuestas
                </button>

                <button class="list-group-item list-group-item-action" id="btnSuscriptores">
                    Suscriptores
                </button>

                <button class="list-group-item list-group-item-action" id="btnAvanzado">
                    Avanzado
                </button>

            </div>
        </div>

        {{-- PANEL PRINCIPAL A LA DERECHA --}}
        <div class="col-md-9 col-lg-10" id="contenidoEstadisticas" style="padding: 25px;">
            <h3 class="mb-4">Seleccione una opción del menú</h3>

            <div id="loader" class="text-center" style="display: none;">
                <div class="spinner-border" role="status"></div>
                <p class="mt-2">Cargando...</p>
            </div>

            {{-- Aquí se inyectará el contenido dinámico (tablas, gráficos, etc.) --}}
            <div id="panelData"></div>
        </div>

    </div>
</div>
@endsection


@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>
$(function () {

    function cargarEstadistica(url) {
        $("#panelData").empty();
        $("#loader").show();

        $.ajax({
            url: url,
            method: "GET",
            success: function (response) {
                $("#loader").hide();

                // Insertamos HTML si viene como vista
                if (response.html) {
                    $("#panelData").html(response.html);
                }

                // Insertamos JSON si es solo data
                else {
                    $("#panelData").html(
                        "<pre>" + JSON.stringify(response, null, 2) + "</pre>"
                    );
                }
            },
            error: function () {
                $("#loader").hide();
                $("#panelData").html(
                    "<div class='alert alert-danger'>Error al cargar los datos.</div>"
                );
            }
        });
    }

    $("#btnEncuestas").click(function () {
        cargarEstadistica("/estadisticas/encuestas");
    });

    $("#btnSuscriptores").click(function () {
        cargarEstadistica("/estadisticas/suscriptores");
    });

    $("#btnAvanzado").click(function () {
        cargarEstadistica("/estadisticas/avanzado");
    });

});
</script>
@endsection
