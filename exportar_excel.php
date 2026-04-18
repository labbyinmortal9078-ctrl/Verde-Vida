<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=plantas_inventario_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF";

$resultado = mysqli_query($conex, "SELECT * FROM especies ORDER BY nombre_comun");

echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
echo "<table border='1'>";
echo "<tr>";
echo "<th>ID</th>";
echo "<th>Nombre Común</th>";
echo "<th>Nombre Científico</th>";
echo "<th>Familia</th>";
echo "<th>Origen</th>";
echo "<th>Tipo de Planta</th>";
echo "<th>Descripción</th>";  
echo "<th>Dificultad de Cultivo</th>";
echo "<th>Fecha de Registro</th>";
echo "</tr>";

while($fila = mysqli_fetch_assoc($resultado)) {
    echo "<tr>";
    echo "<td>" . $fila['ID'] . "</td>";
    echo "<td>" . html_entity_decode($fila['nombre_comun'], ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>" . html_entity_decode($fila['nombre_cientifico'], ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>" . html_entity_decode($fila['familia'], ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>" . html_entity_decode($fila['origen'], ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>" . html_entity_decode($fila['tipo_planta'], ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>" . html_entity_decode($fila['descripcion'], ENT_QUOTES, 'UTF-8') . "</td>";  
    echo "<td>" . html_entity_decode($fila['dificultad_cultivo'], ENT_QUOTES, 'UTF-8') . "</td>";
    echo "<td>" . $fila['hora'] . "</td>";
    echo "</tr>";
}

echo "</table>";

mysqli_close($conex);
exit();
?>