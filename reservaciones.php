<?php
require_once "config/conexion.php";

$mensaje = "";
$tipoMensaje = "";

// CANCELAR RESERVACIÓN
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["cancelar"])) {

    $id_reservacion = $_POST["id_reservacion"] ?? "";

    if (!empty($id_reservacion)) {

        try {

            $sqlEliminar = "DELETE FROM reservaciones WHERE id_reservacion = ?";
            $eliminar = $pdo->prepare($sqlEliminar);
            $eliminar->execute([$id_reservacion]);

            if ($eliminar->rowCount() > 0) {
                $mensaje = "La reservación se canceló correctamente.";
                $tipoMensaje = "success";
            } else {
                $mensaje = "No se encontró la reservación que deseas cancelar.";
                $tipoMensaje = "warning";
            }

        } catch (PDOException $error) {

            $mensaje = "Ocurrió un error al cancelar la reservación.";
            $tipoMensaje = "danger";

            error_log($error->getMessage());
        }
    }
}

// BUSCAR POR FECHA
$fecha = $_GET["fecha"] ?? "";

// CONSULTAR RESERVACIONES
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
        ON r.id_espacio = e.id_espacio";

$datos = [];

if (!empty($fecha)) {
    $sql .= " WHERE r.fecha_reserva = ?";
    $datos[] = $fecha;
}

$sql .= " ORDER BY r.fecha_reserva DESC, r.hora_inicio ASC";

$consulta = $pdo->prepare($sql);
$consulta->execute($datos);

$reservaciones = $consulta->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Sistema de Reservación | Mis Reservaciones</title>

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

                <a href="consultar.php" class="nav-link">
                    Consultar disponibilidad
                </a>

                <a href="reservaciones.php" class="nav-link active">
                    Mis reservaciones
                </a>

                <a href="cancelar.php" class="nav-link">
                    Administrar espacio
                </a>

            </nav>

        </aside>


        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-md-9 col-lg-10 p-4">

            <header class="mb-4">

                <h1 class="h3">Mis reservaciones</h1>

                <p class="text-secondary">
                    Consulta tus reservaciones registradas.
                </p>

            </header>


            <!-- MENSAJE -->
            <?php if (!empty($mensaje)): ?>

                <div class="alert alert-<?= $tipoMensaje ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>

            <?php endif; ?>


            <!-- BUSCAR POR FECHA -->
            <form action="reservaciones.php" method="GET" class="mb-4">

                <div class="row align-items-end">

                    <div class="col-md-5">

                        <label for="fecha" class="form-label">
                            Seleccionar fecha
                        </label>

                        <input
                            type="date"
                            class="form-control"
                            id="fecha"
                            name="fecha"
                            value="<?= htmlspecialchars($fecha) ?>"
                        >

                    </div>

                    <div class="col-md-2">

                        <button type="submit" class="btn btn-primary">
                            Buscar
                        </button>

                    </div>

                </div>

            </form>


            <!-- LISTA DE RESERVACIONES -->

            <?php if (count($reservaciones) > 0): ?>

                <?php foreach ($reservaciones as $r): ?>

                    <!-- TARJETA DE RESERVACION -->
                    <div class="card shadow-sm mb-3">

                        <div class="card-body">

                            <div class="row align-items-center">

                                <!-- INFORMACION DE LA RESERVACION -->
                                <div class="col-md-9">

                                    <h5 class="card-title">
                                        <?= htmlspecialchars($r["nom_espacio"]) ?>
                                    </h5>

                                    <p class="mb-2">
                                        <strong>Responsable:</strong>
                                        <?= htmlspecialchars($r["solicitante"]) ?>
                                    </p>

                                    <p class="mb-2">
                                        <strong>Fecha:</strong>
                                        <?= date("d/m/Y", strtotime($r["fecha_reserva"])) ?>
                                    </p>

                                    <p class="mb-2">
                                        <strong>Horario:</strong>
                                        <?= date("h:i A", strtotime($r["hora_inicio"])) ?>
                                        -
                                        <?= date("h:i A", strtotime($r["hora_fin"])) ?>
                                    </p>

                                    <p class="mb-2">
                                        <strong>Motivo:</strong>
                                        <?= htmlspecialchars($r["motivo"] ?? "") ?>
                                    </p>

                                    <p class="mb-0">
                                        <strong>Estado:</strong>
                                        <?= htmlspecialchars($r["estado"] ?? "Pendiente") ?>
                                    </p>

                                </div>


                                <!-- BOTON CANCELAR -->
                                <div class="col-md-3 text-md-end mt-3 mt-md-0">

                                    <form
                                        action="reservaciones.php<?= !empty($fecha) ? '?fecha=' . urlencode($fecha) : '' ?>"
                                        method="POST"
                                        onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta reservación? Se eliminará permanentemente de la base de datos.');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id_reservacion"
                                            value="<?= $r["id_reservacion"] ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="cancelar"
                                            class="btn btn-danger"
                                        >
                                            Cancelar reservación
                                        </button>

                                    </form>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="card shadow-sm mb-3">

                    <div class="card-body text-center">

                        <p class="mb-0">
                            No hay reservaciones registradas.
                        </p>

                    </div>

                </div>

            <?php endif; ?>

        </main>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>

 
