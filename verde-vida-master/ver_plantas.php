<?php
session_start();
include("conexion.php");
include("permisos.php");

// Verificar permiso para VER plantas
verificarPermiso($conex, $_SESSION['usuario_rol'], 'plantas', 'ver');

$mensaje_exito = '';
if (isset($_GET['registro']) && $_GET['registro'] == 'exitoso') {
    $mensaje_exito = "✅ Planta registrada exitosamente!";
}

// Obtener permisos del usuario actual
$rol = $_SESSION['usuario_rol'];
$puede_eliminar = tienePermiso($conex, $rol, 'plantas', 'eliminar');
$puede_crear = tienePermiso($conex, $rol, 'registrar_planta', 'crear');
$puede_exportar = tienePermiso($conex, $rol, 'exportar', 'ver');

$sql = "SELECT * FROM especies";
$result = mysqli_query($conex, $sql);

$filas_tabla = '';
while($row = mysqli_fetch_assoc($result)) {
    $acciones = '';
    
    // Solo mostrar botón Eliminar si tiene permiso
    if ($puede_eliminar) {
        $acciones .= '
            <button onclick="eliminarPlanta(' . $row['ID'] . ')" 
                    class="btn-danger" 
                    title="Eliminar esta planta">
                    🗑️ Eliminar
            </button>
        ';
    } else {
        $acciones .= '<span style="color: #999;">Sin permisos</span>';
    }
    
    $filas_tabla .= '
    <tr>
        <td>' . htmlspecialchars($row['nombre_comun']) . '</td>
        <td>' . htmlspecialchars($row['nombre_cientifico']) . '</td>
        <td>' . htmlspecialchars($row['familia']) . '</td>
        <td>' . htmlspecialchars($row['origen']) . '</td>
        <td>' . htmlspecialchars($row['tipo_planta']) . '</td>
        <td>' . htmlspecialchars($row['dificultad_cultivo']) . '</td>
        <td>' . $acciones . '</td>
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0f9f4 0%, #d4e8da 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #1a3e30;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.8rem;
            cursor: pointer;
            margin: 2px;
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
            transform: scale(1.02);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        
        th {
            background: linear-gradient(135deg, #1a5f4b 0%, #2d8f6e 100%);
            color: white;
            font-weight: 600;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: #f0f9f4;
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
            flex-wrap: wrap;
        }
        
        .btn {
            background: linear-gradient(135deg, #2d8f6e 0%, #1a5f4b 100%);
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45,143,110,0.4);
        }
        
        .btn-vaciar {
            background-color: #e74c3c;
        }
        
        .btn-vaciar:hover {
            background-color: #c0392b;
        }
        
        .btn-back {
            background: #6c757d;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            margin: 15px 0;
            border-radius: 12px;
            border: 1px solid #c3e6cb;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .sin-permisos {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            th, td {
                padding: 8px;
                font-size: 0.85rem;
            }
            .btn {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <i class="fas fa-seedling"></i> 
            Plantas Registradas
        </h1>
        
        <?php if (!empty($mensaje_exito)): ?>
            <div class="alert-success">
                <?php echo $mensaje_exito; ?>
            </div>
        <?php endif; ?>
        
        <div class="header-actions">
            <div class="left-actions">
                <a href="main.php" class="btn btn-back">
                    ← Volver al Inicio
                </a>
                
                <!-- Botón Nueva Planta - solo si tiene permiso para CREAR -->
                <?php if ($puede_crear): ?>
                    <a href="registrar_plantas.php" class="btn">
                        🌿 Nueva Planta
                    </a>
                <?php endif; ?>
                
                <!-- Botón Exportar - solo si tiene permiso -->
                <?php if ($puede_exportar): ?>
                    <a href="exportar_excel.php" class="btn">
                        📊 Exportar a Excel
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="right-actions">
                <!-- Botón Vaciar Todo - solo si tiene permiso para ELIMINAR -->
                <?php if ($puede_eliminar): ?>
                    <button onclick="vaciarTablaCompleta()" class="btn btn-vaciar">
                        🗑️ Vaciar Todo
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div style="overflow-x: auto;">
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
        <?php else: ?>
            <div class="sin-permisos">
                <i class="fas fa-info-circle"></i> No hay plantas registradas.
            </div>
        <?php endif; ?>
    </div>

    <script>
    function eliminarPlanta(id) {
        if (confirm('¿Estás seguro de que quieres eliminar esta planta?')) {
            window.location.href = 'eliminar_planta.php?id=' + id;
        }
    }
    
    function vaciarTablaCompleta() {
        if (confirm('⚠️ ¿ESTÁS SEGURO?\n\nEsto eliminará TODAS las plantas.\n\nEsta acción NO SE PUEDE DESHACER.')) {
            if (confirm('ÚLTIMA OPORTUNIDAD\n\n¿Realmente quieres eliminar TODOS los datos?')) {
                window.location.href = 'vaciar_tablas.php';
            }
        }
    }
    </script>
</body>
</html>