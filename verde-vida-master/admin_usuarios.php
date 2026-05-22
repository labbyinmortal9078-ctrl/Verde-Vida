<?php
session_start();
include("conexion.php");
include("permisos.php");
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'administrador') {
    header("Location: login.php");
    exit();
}
// Verificar que el usuario es administrado
verificarPermiso($conex, $_SESSION['usuario_rol'], 'usuarios', 'ver');
$mensaje = '';
$tipo_mensaje = '';
// Mensajes después de editar
if (isset($_GET['exito'])) {
    $mensaje = " Rol actualizado correctamente a: " . htmlspecialchars($_GET['rol']);
    $tipo_mensaje = "success";
}
if (isset($_GET['error'])) {
    $mensaje = " Error al actualizar el rol";
    $tipo_mensaje = "error";
}

// BAJA LÓGICA: Desactivar usuario
if (isset($_GET['desactivar'])) {
    $id = intval($_GET['desactivar']);
    $motivo = isset($_GET['motivo']) ? $_GET['motivo'] : 'Desactivado por administrador';
    $fecha = date('Y-m-d H:i:s');
    
    $query = "UPDATE usuarios SET activo = 0, fecha_baja = ?, motivo_baja = ? WHERE ID = ?";
    $stmt = $conex->prepare($query);
    $stmt->bind_param("ssi", $fecha, $motivo, $id);
    
    if ($stmt->execute()) {
        $mensaje = " Usuario desactivado correctamente (baja lógica)";
        $tipo_mensaje = "success";
    } else {
        $mensaje = " Error al desactivar usuario";
        $tipo_mensaje = "error";
    }
}

// ACTIVAR usuario
if (isset($_GET['activar'])) {
    $id = intval($_GET['activar']);
    
    $query = "UPDATE usuarios SET activo = 1, fecha_baja = NULL, motivo_baja = NULL WHERE ID = ?";
    $stmt = $conex->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $mensaje = " Usuario reactivado correctamente";
        $tipo_mensaje = "success";
    } else {
        $mensaje = " Error al reactivar usuario";
        $tipo_mensaje = "error";
    }
}

// BAJA FÍSICA: Borrar permanentemente
if (isset($_GET['borrar_permanente'])) {
    $id = intval($_GET['borrar_permanente']);
    
    $query = "DELETE FROM usuarios WHERE ID = ?";
    $stmt = $conex->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $mensaje = "⚠️ Usuario eliminado PERMANENTEMENTE (baja física)";
        $tipo_mensaje = "error";
    } else {
        $mensaje = "❌ Error al eliminar usuario";
        $tipo_mensaje = "error";
    }
}

// Obtener todos los usuarios
$query = "SELECT ID, nombre, apellido, email, rol, activo, fecha_baja, motivo_baja, fecha_contratacion 
            FROM usuarios ORDER BY activo DESC, ID ASC";
$result = $conex->query($query);
$usuarios = $result->fetch_all(MYSQLI_ASSOC);

// Contar estadísticas
$total_usuarios = count($usuarios);
$activos = count(array_filter($usuarios, function($u) { return $u['activo'] == 1; }));
$inactivos = $total_usuarios - $activos;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Usuarios - Verde Vida</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f9f4 0%, #d4e8da 100%);
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(135deg, #1a5f4b 0%, #0d3b2e 100%);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.8rem;
            font-weight: 800;
            color: white;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .nav-link {
            background: rgba(255,255,255,0.15);
            color: white;
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.3);
        }

        .main-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header h1 {
            color: #1a3e30;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1a5f4b;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .table-container {
            background: white;
            border-radius: 24px;
            overflow-x: auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #f8f9fa;
            color: #1a3e30;
            font-weight: 600;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-active {
            background: #d4edda;
            color: #155724;
        }

        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-admin {
            background: #cce5ff;
            color: #004085;
        }

        .badge-jardinero {
            background: #d4edda;
            color: #155724;
        }

        .badge-empleado {
            background: #fff3cd;
            color: #856404;
        }

        .badge-user {
            background: #e2e3e5;
            color: #383d41;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin: 2px;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-primary {
            background: #2d8f6e;
            color: white;
        }

        .btn-primary:hover {
            background: #1a5f4b;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: #e9ecef;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            color: #495057;
            transition: all 0.3s;
        }

        .filter-btn.active {
            background: #2d8f6e;
            color: white;
        }

        @media (max-width: 768px) {
            th, td {
                padding: 0.75rem;
                font-size: 0.85rem;
            }
            .btn {
                padding: 4px 8px;
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="main.php" class="logo">
            <i class="fas fa-leaf"></i>
            <span>Verde Vida</span>
        </a>
        <div class="nav-links">
            <a href="main.php" class="nav-link"><i class="fas fa-home"></i> Inicio</a>
            <a href="ver_plantas.php" class="nav-link"><i class="fas fa-seedling"></i> Plantas</a>
            <a href="admin_usuarios.php" class="nav-link"><i class="fas fa-users"></i> Usuarios</a>
            <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </nav>

    <div class="main-container">
        <div class="header">
            <h1><i class="fas fa-users-cog"></i> Administrar Usuarios</h1>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_usuarios; ?></div>
                <div class="stat-label">Total Usuarios</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #28a745;"><?php echo $activos; ?></div>
                <div class="stat-label">Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #dc3545;"><?php echo $inactivos; ?></div>
                <div class="stat-label">Inactivos (Baja Lógica)</div>
            </div>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="filters">
            <a href="?filtro=todos" class="filter-btn <?php echo (!isset($_GET['filtro']) || $_GET['filtro'] == 'todos') ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> Todos
            </a>
            <a href="?filtro=activos" class="filter-btn <?php echo (isset($_GET['filtro']) && $_GET['filtro'] == 'activos') ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i> Activos
            </a>
            <a href="?filtro=inactivos" class="filter-btn <?php echo (isset($_GET['filtro']) && $_GET['filtro'] == 'inactivos') ? 'active' : ''; ?>">
                <i class="fas fa-ban"></i> Inactivos
            </a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <?php
                        $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
                        if ($filtro == 'activos' && $usuario['activo'] == 0) continue;
                        if ($filtro == 'inactivos' && $usuario['activo'] == 1) continue;
                        ?>
                        <tr>
                            <td><?php echo $usuario['ID']; ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td>
                                <?php if ($usuario['rol'] == 'administrador'): ?>
                                    <span class="badge badge-admin"><i class="fas fa-crown"></i> Administrador</span>
                                <?php elseif ($usuario['rol'] == 'jardinero'): ?>
                                    <span class="badge badge-jardinero"><i class="fas fa-leaf"></i> Jardinero</span>
                                <?php elseif ($usuario['rol'] == 'empleado'): ?>
                                    <span class="badge badge-empleado"><i class="fas fa-briefcase"></i> Empleado</span>
                                <?php else: ?>
                                    <span class="badge badge-user"><i class="fas fa-user"></i> Usuario</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($usuario['activo'] == 1): ?>
                                    <span class="badge badge-active"><i class="fas fa-check-circle"></i> Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-inactive"><i class="fas fa-ban"></i> Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $usuario['fecha_contratacion']; ?></td>
                            <td>
                                <!-- EDITAR -->
                                <a href="editar_usuario.php?id=<?php echo $usuario['ID']; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                
                                <?php if ($usuario['activo'] == 1): ?>
                                    <!-- BAJA LÓGICA -->
                                    <a href="?desactivar=<?php echo $usuario['ID']; ?>&motivo=Desactivado por administrador" 
                                    class="btn btn-warning" 
                                    onclick="return confirm('¿Desactivar este usuario? (Baja lógica - Puede reactivarse después)')">
                                        <i class="fas fa-user-slash"></i> Desactivar
                                    </a>
                                <?php else: ?>
                                    <!-- REACTIVAR -->
                                    <a href="?activar=<?php echo $usuario['ID']; ?>" 
                                    class="btn btn-success" 
                                    onclick="return confirm('¿Reactivar este usuario?')">
                                        <i class="fas fa-user-check"></i> Activar
                                    </a>
                                <?php endif; ?>
                                
                                <!-- BAJA FÍSICA (solo para administradores) -->
                                <?php if ($usuario['ID'] != $_SESSION['usuario_id']): ?>
                                    <a href="?borrar_permanente=<?php echo $usuario['ID']; ?>" 
                                    class="btn btn-danger" 
                                    onclick="return confirm(' ¡ATENCIÓN! Esto BORRARÁ PERMANENTEMENTE al usuario. ¿Estás seguro?')">
                                        <i class="fas fa-trash-alt"></i> Borrar
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        
</html>