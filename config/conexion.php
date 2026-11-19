
<?php

$host = "localhost";
$baseDatos = "sistema_reservaciones";
$usuario = "tuespacio";
$contrasena = "TuEspacio2026_DB#91";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$baseDatos;charset=utf8mb4",
        $usuario,
        $contrasena,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $error) {

    error_log($error->getMessage());

    die("Error: No fue posible conectar con la base de datos.");

}

?>



