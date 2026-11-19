<?php
require_once "config/conexion.php";

$mensaje = "";
$tipoMensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $espacio = trim($_POST["espacio"] ?? "");
    $responsable = trim($_POST["responsable"] ?? "");
    $fecha = $_POST["fecha"] ?? "";
    $hora_inicio = $_POST["hora_inicio"] ?? "";
    $hora_termino = $_POST["hora_termino"] ?? "";
    $motivo = trim($_POST["motivo"] ?? "");

    if (
        empty($espacio) ||
        empty($responsable) ||
        empty($fecha) ||
        empty($hora_inicio) ||
        empty($hora_termino) ||
        empty($motivo)
    ) {

        $mensaje = "Completa todos los campos solicitados.";
        $tipoMensaje = "warning";

    } elseif ($hora_termino <= $hora_inicio) {

        $mensaje = "La hora de término debe ser posterior a la hora de inicio.";
        $tipoMensaje = "warning";

    } else {

        try {

            // Iniciar una transacción
            $pdo->beginTransaction();

            // Buscar si el espacio ya existe
            $consulta = $pdo->prepare(
                "SELECT id_espacio
                 FROM espacios
                 WHERE nom_espacio = ?"
            );

            $consulta->execute([$espacio]);

            $resultado = $consulta->fetch();

            if ($resultado) {

                $id_espacio = $resultado["id_espacio"];

            } else {

                // Registrar el espacio si todavía no existe
                $nuevoEspacio = $pdo->prepare(
                    "INSERT INTO espacios (nom_espacio)
                     VALUES (?)"
                );

                $nuevoEspacio->execute([$espacio]);

                $id_espacio = $pdo->lastInsertId();
            }

            /*
             * Bloquear el espacio mientras se realiza
             * la comprobación y el registro.
             * Esto ayuda a evitar que dos usuarios
             * lo reserven al mismo tiempo.
             */
            $bloquear = $pdo->prepare(
                "SELECT id_espacio
                 FROM espacios
                 WHERE id_espacio = ?
                 FOR UPDATE"
            );

            $bloquear->execute([$id_espacio]);

            // Comprobar si existe una reservación que se cruce
            $verificar = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM reservaciones
                 WHERE id_espacio = ?
                 AND fecha_reserva = ?
                 AND hora_inicio < ?
                 AND hora_fin > ?"
            );

            $verificar->execute([
                $id_espacio,
                $fecha,
                $hora_termino,
                $hora_inicio
            ]);

            $existe = $verificar->fetchColumn();

            if ($existe > 0) {

                // No permitir guardar la reservación
                $pdo->rollBack();

                $mensaje = "Este espacio ya está reservado en ese horario. Selecciona otro horario.";
                $tipoMensaje = "warning";

            } else {

                // Guardar la reservación
                $sql = "INSERT INTO reservaciones
                        (id_espacio, solicitante, fecha_reserva,
                         hora_inicio, hora_fin, motivo, estado)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";

                $guardar = $pdo->prepare($sql);

                $guardar->execute([
                    $id_espacio,
                    $responsable,
                    $fecha,
                    $hora_inicio,
                    $hora_termino,
                    $motivo,
                    "Pendiente"
                ]);

                // Confirmar el registro
                $pdo->commit();

                $mensaje = "¡La reservación se guardó correctamente!";
                $tipoMensaje = "success";

                // Limpiar los campos después de guardar
                $_POST = [];
            }

        } catch (PDOException $error) {

            // Deshacer cambios si ocurrió un error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            // Mensaje cuando MySQL detecta un horario ocupado
            if ($error->getCode() == "45000") {

                $mensaje = "Este espacio ya está reservado en ese horario. Selecciona otro horario.";
                $tipoMensaje = "warning";

            } else {

                $mensaje = "Ocurrió un error al guardar la reservación.";
                $tipoMensaje = "danger";

                error_log($error->getMessage());
            }
        }
    }
}
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

                <a href="registrar.php" class="nav-link active">
                    Registrar reservación
                </a>

                <a href="consultar.php" class="nav-link">
                    Consultar disponibilidad
                </a>

                <a href="reservaciones.php" class="nav-link">
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

                <h1 class="h3">Registrar reservación</h1>

                <p class="text-secondary">
                    Completa la información para registrar una nueva reservación.
                </p>

            </header>


            <!-- MENSAJE -->
            <?php if (!empty($mensaje)): ?>

                <div class="alert alert-<?= $tipoMensaje ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>

            <?php endif; ?>


            <!-- FORMULARIO -->
            <form action="registrar.php" method="POST">

                <div class="row g-3">


                    <!-- ESPACIO -->
                    <div class="col-md-6">

                        <label for="espacio" class="form-label">
                            Espacio
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="espacio"
                            name="espacio"
                            placeholder="Escribe el espacio que deseas reservar"
                            value="<?= htmlspecialchars($_POST['espacio'] ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- RESPONSABLE -->
                    <div class="col-md-6">

                        <label for="responsable" class="form-label">
                            Responsable
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="responsable"
                            name="responsable"
                            placeholder="Nombre del responsable"
                            value="<?= htmlspecialchars($_POST['responsable'] ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- FECHA -->
                    <div class="col-md-6">

                        <label for="fecha" class="form-label">
                            Fecha
                        </label>

                        <input
                            type="date"
                            class="form-control"
                            id="fecha"
                            name="fecha"
                            value="<?= htmlspecialchars($_POST['fecha'] ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- HORA DE INICIO -->
                    <div class="col-md-3">

                        <label for="hora_inicio" class="form-label">
                            Hora de inicio
                        </label>

                        <input
                            type="time"
                            class="form-control"
                            id="hora_inicio"
                            name="hora_inicio"
                            value="<?= htmlspecialchars($_POST['hora_inicio'] ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- HORA DE TERMINO -->
                    <div class="col-md-3">

                        <label for="hora_termino" class="form-label">
                            Hora de término
                        </label>

                        <input
                            type="time"
                            class="form-control"
                            id="hora_termino"
                            name="hora_termino"
                            value="<?= htmlspecialchars($_POST['hora_termino'] ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- MOTIVO -->
                    <div class="col-12">

                        <label for="motivo" class="form-label">
                            Motivo
                        </label>

                        <textarea
                            class="form-control"
                            id="motivo"
                            name="motivo"
                            rows="4"
                            placeholder="Describe el motivo de la reservación"
                            required
                        ><?= htmlspecialchars($_POST['motivo'] ?? '') ?></textarea>

                    </div>


                    <!-- BOTONES -->
                    <div class="col-12 mt-4">

                        <button type="reset" class="btn btn-outline-secondary">
                            Limpiar
                        </button>

                        <button type="submit" class="btn btn-primary">
                            Guardar
                        </button>

                    </div>

                </div>

            </form>

        </main>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>