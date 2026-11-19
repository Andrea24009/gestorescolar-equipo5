<?php
require_once "config/conexion.php";
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


                <a href="index.php" class="nav-link active">
                    Inicio
                </a>


                <a href="registrar.php" class="nav-link ">
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
        <main class="col-md-9 col-lg-10 d-flex justify-content-center align-items-center text-center"
              style="min-height: 80vh;">


            <div>


                <h1 class="display-5 fw-bold text-dark mb-3">
                    ¡BIENVENIDO!
                </h1>


                <p class="text-dark mb-4">
                    Selecciona una opción del<br>
                    menú para comenzar.
                </p>


                <div>
                    <i class="bi bi-calendar-date-fill text-danger"
                       style="font-size: 110px;"></i>
                </div>


            </div>


        </main>


    </div>
</div>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body> </html>


