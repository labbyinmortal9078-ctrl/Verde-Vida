<?php
session_start();
include_once("conexion.php");
include_once("auditoria.php");

// Registrar la acción de cierre de sesión
registrarAuditoria($conex, "Cierre de Sesión", "usuarios", $_SESSION['usuario_id']);

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

header("Location: login.php");
exit();
?>