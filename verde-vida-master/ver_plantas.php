<?php
session_start();
include("conexion.php");


$mensaje_exito = '';
if (isset($_GET['registro']) && $_GET['registro'] == 'exitoso') {
    $mensaje_exito = " Planta registrada exitosamente!";
}


$sql = "SELECT * FROM especies";
$result = mysqli_query($conex, $sql);

$filas_tabla = '';
while($row = mysqli_fetch_assoc($result)) {
    $filas_tabla .= '
    <tr>
        <td>' . htmlspecialchars($row['nombre_comun']) . '</td>
        <td>' . htmlspecialchars($row['nombre_cientifico']) . '</td>
        <td>' . htmlspecialchars($row['familia']) . '</td>
        <td>' . htmlspecialchars($row['origen']) . '</td>
        <td>' . htmlspecialchars($row['tipo_planta']) . '</td>
        <td>' . htmlspecialchars($row['dificultad_cultivo']) . '</td>
        <td>
            <button onclick="eliminarPlanta(' . $row['ID'] . ')" 
                    class="btn-danger" 
                    title="Eliminar esta planta">
                    Eliminar
            </button>
        </td>
    </tr>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plantas Registradas - Verde Vida</title>
    <style>
        .btn-danger {
            background-color: #e74c3c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
            margin: 5px;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #2e7d32;
            color: white;
        }
        
        .header-actions {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .left-actions, .right-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            background-color: #2e7d32;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #1b5e20;
        }
    </style>
</head>
<body>
    <header>
        
    </header>
    
    <div class="container">
        <h1>Plantas Registradas</h1>
        
        <?php if (!empty($mensaje_exito)): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; margin: 15px 0; border-radius: 4px; border: 1px solid #c3e6cb;">
                <?php echo $mensaje_exito; ?>
            </div>
        <?php endif; ?>
        
        <div class="header-actions">
            <div class="left-actions">
                <a href="main.php" class="btn">← Volver al Inicio</a>
                <a href="registrar_plantas.php" class="btn">🌿 Nueva Planta</a>
                <a href="exportar_excel.php" class="btn">📊 Exportar a Excel</a>
            </div>
            <div class="right-actions">
                <button onclick="vaciarTablaCompleta()" class="btn-danger">
                        Vaciar Todo
                </button>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Nombre Común</th>
                    <th>Nombre Científico</th>
                    <th>Familia</th>
                    <th>Origen</th>
                    <th>Tipo</th>
                    <th>Dificultad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $filas_tabla; ?>
            </tbody>
        </table>
        
    </div>

    <script>
    function eliminarPlanta(id) {
        if (confirm('¿Estás seguro de que quieres eliminar esta planta?')) {
            window.location.href = 'eliminar_planta.php?id=' + id;
        }
    }
    
    function vaciarTablaCompleta() {
        if (confirm('¿ESTÁS SEGURO?\n\nEsto eliminará TODAS las plantas y su inventario.\n\nSe eliminarán: ' + document.querySelectorAll('tbody tr').length + ' plantas\n\n ESTA ACCIÓN NO SE PUEDE DESHACER')) {
            if (confirm('ÚLTIMA OPORTUNIDAD\n\n¿Realmente quieres eliminar TODOS los datos?')) {
                window.location.href = 'vaciar_tablas.php';
            }
        }
    }
    </script>
</body>
</html>