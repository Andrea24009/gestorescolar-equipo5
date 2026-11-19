<?php
require_once "config/conexion.php";

// Obtener el filtro de fecha
$fecha = $_GET["fecha"] ?? "";

// Consulta de reservaciones
$sql = "SELECT
            r.id_reservacion,
            e.nom_espacio,
            r.solicitante,
            r.fecha_reserva,
            r.hora_inicio,
            r.hora_fin,
            r.motivo,
            r.estado
        FROM reservaciones r
        INNER JOIN espacios e
        ON r.id_espacio = e.id_espacio
        WHERE 1=1";

$datos = [];

if (!empty($fecha)) {
    $sql .= " AND r.fecha_reserva = ?";
    $datos[] = $fecha;
}

$sql .= " ORDER BY r.fecha_reserva, r.hora_inicio";

$consulta = $pdo->prepare($sql);
$consulta->execute($datos);

$reservaciones = $consulta->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Sistema de Reservación | Consultar Reservaciones</title>

<link rel="stylesheet" href="css/estilo.css">

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

</head>

<body>

<!-- ENCABEZADO -->
<header>

<nav class="navbar navbar-dark" style="background: linear-gradient(90deg, #060c20, #1e3a8a) !important;">

    <div class="container-fluid">

        <div>

            <img src="css/colegio.png" class="img-logo">

            <a href="index.php" class="navbar-brand logo">
                TuEspacio
            </a>

        </div>

    </div>

</nav>

</header>


<div class="container-fluid">

    <div class="row">

        <!-- MENU LATERAL -->
        <aside class="col-md-3 col-lg-2 sidebar p-3">

            <h6 class="text-secondary mb-3">MENÚ</h6>

            <nav class="nav flex-column">

                <a href="index.php" class="nav-link">
                    Inicio
                </a>

                <a href="registrar.php" class="nav-link">
                    Registrar reservación
                </a>

                <a href="consultar.php" class="nav-link active">
                    Consultar disponibilidad
                </a>

                <a href="reservaciones.php" class="nav-link">
                    Mis reservaciones
                </a>

                <a href="cancelar.php" class="nav-link">
                    Administrar espacios
                </a>

            </nav>

        </aside>


        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-md-9 col-lg-10 p-4">

            <header class="mb-4">

                <h1 class="h3">Consultar reservaciones</h1>

                <p class="text-secondary">
                    Consulta las reservaciones registradas, sus fechas y estados.
                </p>

                <!-- FORMULARIO DE FILTRO -->
                <form action="consultar.php" method="GET">

                    <div class="mb-3">

                        <label for="fecha" class="form-label">
                            Fecha de reservación
                        </label>

                        <input
                            type="date"
                            id="fecha"
                            name="fecha"
                            class="form-control"
                            value="<?= htmlspecialchars($fecha) ?>"
                        >

                    </div>

                    <button type="submit" class="btn btn-primary">
                        Consultar
                    </button>

                    <a href="consultar.php" class="btn btn-outline-secondary">
                        Limpiar
                    </a>

                </form>

            </header>


            <!-- TABLA DE RESERVACIONES -->
            <div class="mb-3">

                <h5>Lista de reservaciones</h5>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead class="table-light">

                            <tr>
                                <th>ESPACIO</th>
                                <th>HORARIO</th>
                                <th>FECHA</th>
                                <th>ESTADO</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php if (count($reservaciones) > 0): ?>

                                <?php foreach ($reservaciones as $r): ?>

                                    <tr>

                                        <td>
                                            <?= htmlspecialchars($r["nom_espacio"]) ?>
                                        </td>

                                        <td>
                                            <?= date("h:i A", strtotime($r["hora_inicio"])) ?>
                                            -
                                            <?= date("h:i A", strtotime($r["hora_fin"])) ?>
                                        </td>

                                        <td>
                                            <?= date("d/m/Y", strtotime($r["fecha_reserva"])) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($r["estado"] ?? "Pendiente") ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="4" class="text-center">
                                        No hay reservaciones registradas.
                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </main>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>