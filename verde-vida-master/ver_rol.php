<?php
session_start();
echo "Tu rol es: " . ($_SESSION['usuario_rol'] ?? 'NO DEFINIDO');
?>