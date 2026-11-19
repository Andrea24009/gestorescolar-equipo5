<?php
require_once "config/conexion.php";

$mensaje = "";
$tipoMensaje = "";

// REGISTRAR NUEVO ESPACIO
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $responsable = trim($_POST["responsable"] ?? "");
    $ubicacion = trim($_POST["ubicacion"] ?? "");

    if (empty($nombre) || empty($responsable) || empty($ubicacion)) {

        $mensaje = "Completa todos los campos solicitados.";
        $tipoMensaje = "warning";

    } else {

        try {

            // Comprobar si el espacio ya existe
            $consulta = $pdo->prepare(
                "SELECT id_espacio FROM espacios WHERE nom_espacio = ?"
            );

            $consulta->execute([$nombre]);

            if ($consulta->fetch()) {

                $mensaje = "Este espacio ya se encuentra registrado.";
                $tipoMensaje = "warning";

            } else {

                // Guardar el nuevo espacio
                $sql = "INSERT INTO espacios
                        (nom_espacio, responsable, ubicacion)
                        VALUES (?, ?, ?)";

                $guardar = $pdo->prepare($sql);

                $guardar->execute([
                    $nombre,
                    $responsable,
                    $ubicacion
                ]);

                $mensaje = "¡El nuevo espacio se registró correctamente!";
                $tipoMensaje = "success";

                $_POST = [];
            }

        } catch (PDOException $error) {

            $mensaje = "Ocurrió un error al registrar el espacio.";
            $tipoMensaje = "danger";

            error_log($error->getMessage());
        }
    }
}

// CONSULTAR ESPACIOS REGISTRADOS
$consultaEspacios = $pdo->query(
    "SELECT * FROM espacios ORDER BY id_espacio DESC"
);

$espacios = $consultaEspacios->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Sistema de Reservación | Administrar espacios</title>

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

                <a href="reservaciones.php" class="nav-link">
                    Mis reservaciones
                </a>

                <a href="cancelar.php" class="nav-link active">
                    Administrar espacios
                </a>

            </nav>

        </aside>


        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-md-9 col-lg-10 p-4">

            <header class="mb-4">

                <h1 class="h3">Administrar espacios</h1>

                <p class="text-secondary">
                    Registra nuevos espacios para que puedan ser reservados.
                </p>

            </header>


            <!-- MENSAJE -->
            <?php if (!empty($mensaje)): ?>

                <div class="alert alert-<?= $tipoMensaje ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>

            <?php endif; ?>


            <!-- FORMULARIO -->
            <section class="card border-0 shadow-sm mb-4">

                <div class="card-body">

                    <h5 class="card-title mb-4">
                        Registrar nuevo espacio
                    </h5>

                    <form action="cancelar.php" method="POST">

                        <div class="row g-3">


                            <!-- NOMBRE DEL ESPACIO -->
                            <div class="col-md-6">

                                <label for="nombre" class="form-label">
                                    Nombre del espacio
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="nombre"
                                    name="nombre"
                                    placeholder="Ejemplo: Salón 4"
                                    value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
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


                            <!-- UBICACION -->
                            <div class="col-12">

                                <label for="ubicacion" class="form-label">
                                    Ubicación
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="ubicacion"
                                    name="ubicacion"
                                    placeholder="Ejemplo: Edificio B, planta baja"
                                    value="<?= htmlspecialchars($_POST['ubicacion'] ?? '') ?>"
                                    required
                                >

                            </div>


                            <!-- BOTONES -->
                            <div class="col-12 mt-4">

                                <button
                                    type="reset"
                                    class="btn btn-outline-secondary"
                                >
                                    Limpiar
                                </button>

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Registrar espacio
                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </section>


            <!-- LISTA DE ESPACIOS -->
            <section class="card border-0 shadow-sm">

                <div class="card-body">

                    <h5 class="card-title mb-4">
                        Espacios registrados
                    </h5>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>

                                <tr>

                                    <th>Nombre del espacio</th>
                                    <th>Responsable</th>
                                    <th>Ubicación</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php if (count($espacios) > 0): ?>

                                    <?php foreach ($espacios as $espacio): ?>

                                        <tr>

                                            <td>
                                                <?= htmlspecialchars($espacio["nom_espacio"]) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($espacio["responsable"] ?? "No asignado") ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($espacio["ubicacion"] ?? "No especificada") ?>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>

                                        <td colspan="3" class="text-center">
                                            No hay espacios registrados.
                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </section>

        </main>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>