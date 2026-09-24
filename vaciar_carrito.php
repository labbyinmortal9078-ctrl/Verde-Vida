<?php
session_start();
include_once("conexion.php");
include_once("carrito_funciones.php");
include_once("alertas.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

vaciarCarrito($conex, $_SESSION['usuario_id']);

mostrarNotificacion('advertencia', 'Carrito vaciado correctamente');
header("Location: carrito.php");
exit();