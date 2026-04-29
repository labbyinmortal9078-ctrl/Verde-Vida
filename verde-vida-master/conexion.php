<?php
$host = 'localhost';
$user = 'root';
$password = '9078';
$database = 'vivero4';

$conex = mysqli_connect($host, $user, $password, $database);

// Verificar conexión sin mostrar errores en la página
if (!$conex) {
    // En lugar de mostrar "Error:" en la pantalla, guardarlo en un log
    error_log("Error de conexión a la base de datos: " . mysqli_connect_error());
    die("Error de conexión. Contacte al administrador.");
}

// Establecer charset para evitar problemas con la ñ
mysqli_set_charset($conex, "utf8");
?>