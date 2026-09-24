<?php
session_start();
include_once("conexion.php");
include_once("permisos.php");
include_once("auditoria.php");
include_once("alertas.php");
include_once("carrito_funciones.php");

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

verificarPermiso($conex, $_SESSION['usuario_rol'], 'usuarios', 'ver');

// Badge del carrito
$total_items_carrito = contarItemsCarrito($conex, $_SESSION['usuario_id']);

// ============================================================
// MENSAJES DESPUÉS DE EDITAR ROL
// ============================================================
if (isset($_GET['exito'])) {
    mostrarNotificacion('exito', 'Rol actualizado correctamente a: ' . htmlspecialchars($_GET['rol'] ?? ''));
    header("Location: admin_usuarios.php");
    exit();
}

if (isset($_GET['error']) && $_GET['error'] === 'no_puedes_cambiarte') {
    mostrarNotificacion('error', 'No puedes cambiar tu propio rol a algo diferente de administrador');
    header("Location: admin_usuarios.php");
    exit();
}

// ============================================================
// DESACTIVAR USUARIO
// ============================================================
if (isset($_GET['desactivar'])) {
    $id     = (int) $_GET['desactivar'];
    $motivo = $_GET['motivo'] ?? 'Desactivado por administrador';
    $fecha  = date('Y-m-d H:i:s');

    $stmtGet = $conex->prepare("SELECT nombre, apellido, email, rol, activo, fecha_baja, motivo_baja FROM usuarios WHERE ID = ?");
    $stmtGet->bind_param("i", $id);
    $stmtGet->execute();
    $antes = $stmtGet->get_result()->fetch_assoc();
    $stmtGet->close();

    if (!$antes) {
        mostrarNotificacion('error', 'El usuario no existe');
        header("Location: admin_usuarios.php");
        exit();
    }

    $query = "UPDATE usuarios SET activo = 0, fecha_baja = ?, motivo_baja = ? WHERE ID = ?";
    $stmt  = $conex->prepare($query);
    $stmt->bind_param("ssi", $fecha, $motivo, $id);

    if ($stmt->execute()) {
        registrarAuditoria(
            $conex,
            'Desactivar usuario',
            'usuarios',
            $id,
            json_encode($antes),
            json_encode([
                'activo'      => 0,
                'fecha_baja'  => $fecha,
                'motivo_baja' => $motivo
            ])
        );
        mostrarNotificacion('exito', 'Usuario desactivado correctamente');
    } else {
        mostrarNotificacion('error', 'Error al desactivar usuario');
    }
    header("Location: admin_usuarios.php");
    exit();
}

// ============================================================
// ACTIVAR USUARIO
// ============================================================
if (isset($_GET['activar'])) {
    $id = (int) $_GET['activar'];

    $stmtGet = $conex->prepare("SELECT nombre, apellido, email, rol, activo, fecha_baja, motivo_baja FROM usuarios WHERE ID = ?");
    $stmtGet->bind_param("i", $id);
    $stmtGet->execute();
    $antes = $stmtGet->get_result()->fetch_assoc();
    $stmtGet->close();

    if (!$antes) {
        mostrarNotificacion('error', 'El usuario no existe');
        header("Location: admin_usuarios.php");
        exit();
    }

    $query = "UPDATE usuarios SET activo = 1, fecha_baja = NULL, motivo_baja = NULL WHERE ID = ?";
    $stmt  = $conex->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        registrarAuditoria(
            $conex,
            'Activar usuario',
            'usuarios',
            $id,
            json_encode($antes),
            json_encode(['activo' => 1])
        );
        mostrarNotificacion('exito', 'Usuario reactivado correctamente');
    } else {
        mostrarNotificacion('error', 'Error al reactivar usuario');
    }
    header("Location: admin_usuarios.php");
    exit();
}

// ============================================================
// BAJA FÍSICA
// ============================================================
if (isset($_GET['borrar_permanente'])) {
    $id = (int) $_GET['borrar_permanente'];

    $stmtGet = $conex->prepare("SELECT nombre, apellido, email, rol, activo, fecha_baja, motivo_baja FROM usuarios WHERE ID = ?");
    $stmtGet->bind_param("i", $id);
    $stmtGet->execute();
    $antes = $stmtGet->get_result()->fetch_assoc();
    $stmtGet->close();

    if (!$antes) {
        mostrarNotificacion('error', 'El usuario no existe');
        header("Location: admin_usuarios.php");
        exit();
    }

    $stmt = $conex->prepare("DELETE FROM usuarios WHERE ID = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        registrarAuditoria(
            $conex,
            'Eliminar usuario',
            'usuarios',
            $id,
            json_encode($antes),
            null
        );
        mostrarNotificacion('advertencia', '⚠️ Usuario eliminado permanentemente');
    } else {
        mostrarNotificacion('error', 'Error al eliminar usuario');
    }
    header("Location: admin_usuarios.php");
    exit();
}

// ============================================================
// LISTAR USUARIOS
// ============================================================
$query = "SELECT ID, nombre, apellido, email, rol, activo, fecha_baja, motivo_baja, fecha_contratacion
        FROM usuarios ORDER BY activo DESC, ID ASC";
$result  = $conex->query($query);
$usuarios = $result->fetch_all(MYSQLI_ASSOC);

$total_usuarios = count($usuarios);
$activos   = count(array_filter($usuarios, fn($u) => $u['activo'] == 1));
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
        * { margin: 0; padding: 0; box-sizing: border-box; }

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
        .nav-links { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
        .nav-link {
            background: rgba(255,255,255,0.15);
            color: white;
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s;
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .nav-link:hover { background: rgba(255,255,255,0.3); }
        .nav-link.carrito-link { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
        .badge-carrito {
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 7px;
            font-size: 0.7rem;
            font-weight: 700;
            margin-left: 4px;
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
        .stat-number { font-size: 2rem; font-weight: 700; color: #1a5f4b; }
        .stat-label  { color: #666; font-size: 0.9rem; }

        .table-container {
            background: white;
            border-radius: 24px;
            overflow-x: auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        table { width: 100%; border-collapse: collapse; }
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
        tr:hover { background: #f9f9f9; }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-active     { background: #d4edda; color: #155724; }
        .badge-inactive   { background: #f8d7da; color: #721c24; }
        .badge-admin      { background: #cce5ff; color: #004085; }
        .badge-jardinero  { background: #d4edda; color: #155724; }
        .badge-empleado   { background: #fff3cd; color: #856404; }
        .badge-user       { background: #e2e3e5; color: #383d41; }

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
        .btn-danger  { background: #dc3545; color: white; }
        .btn-danger:hover  { background: #c82333; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-warning:hover { background: #e0a800; }
        .btn-primary { background: #2d8f6e; color: white; }
        .btn-primary:hover { background: #1a5f4b; }

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
        .filter-btn.active { background: #2d8f6e; color: white; }

        @media (max-width: 768px) {
            th, td { padding: 0.75rem; font-size: 0.85rem; }
            .btn { padding: 4px 8px; font-size: 0.7rem; }
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
            <a href="carrito.php" class="nav-link carrito-link">
                <i class="fas fa-shopping-cart"></i> Carrito
                <?php if ($total_items_carrito > 0): ?>
                    <span class="badge-carrito"><?php echo $total_items_carrito; ?></span>
                <?php endif; ?>
            </a>
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

        <div class="filters">
            <a href="?filtro=todos" class="filter-btn <?php echo (!isset($_GET['filtro']) || $_GET['filtro'] === 'todos') ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> Todos
            </a>
            <a href="?filtro=activos" class="filter-btn <?php echo (($_GET['filtro'] ?? '') === 'activos') ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i> Activos
            </a>
            <a href="?filtro=inactivos" class="filter-btn <?php echo (($_GET['filtro'] ?? '') === 'inactivos') ? 'active' : ''; ?>">
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
                    <?php
                    $filtro = $_GET['filtro'] ?? 'todos';
                    foreach ($usuarios as $usuario):
                        if ($filtro === 'activos'   && $usuario['activo'] == 0) continue;
                        if ($filtro === 'inactivos' && $usuario['activo'] == 1) continue;

                        $nombreCompleto = htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']);
                        $esUnoMismo = ($usuario['ID'] == $_SESSION['usuario_id']);
                    ?>
                    <tr>
                        <td><?php echo $usuario['ID']; ?></td>
                        <td><?php echo $nombreCompleto; ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td>
                            <?php
                            $rolBadges = [
                                'administrador' => ['badge-admin',     'fa-crown',     'Administrador'],
                                'jardinero'     => ['badge-jardinero', 'fa-leaf',      'Jardinero'],
                                'empleado'      => ['badge-empleado',  'fa-briefcase', 'Empleado'],
                            ];
                            [$clase, $icono, $texto] = $rolBadges[$usuario['rol']] ?? ['badge-user', 'fa-user', 'Usuario'];
                            ?>
                            <span class="badge <?php echo $clase; ?>">
                                <i class="fas <?php echo $icono; ?>"></i> <?php echo $texto; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($usuario['activo'] == 1): ?>
                                <span class="badge badge-active"><i class="fas fa-check-circle"></i> Activo</span>
                            <?php else: ?>
                                <span class="badge badge-inactive"><i class="fas fa-ban"></i> Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($usuario['fecha_contratacion'] ?? '-'); ?></td>
                        <td>
                            <?php if (!$esUnoMismo): ?>
                                <a href="editar_usuario.php?id=<?php echo $usuario['ID']; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            <?php else: ?>
                                <span class="btn" style="background:#6c757d;color:white;cursor:not-allowed;opacity:.6;">
                                    <i class="fas fa-lock"></i> No puedes editarte
                                </span>
                            <?php endif; ?>

                            <?php if ($usuario['activo'] == 1): ?>
                                <button type="button" class="btn btn-warning"
                                        onclick="desactivarUsuario(<?php echo $usuario['ID']; ?>, '<?php echo addslashes($nombreCompleto); ?>')">
                                    <i class="fas fa-user-slash"></i> Desactivar
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-success"
                                        onclick="reactivarUsuario(<?php echo $usuario['ID']; ?>, '<?php echo addslashes($nombreCompleto); ?>')">
                                    <i class="fas fa-user-check"></i> Activar
                                </button>
                            <?php endif; ?>

                            <?php if (!$esUnoMismo): ?>
                                <button type="button" class="btn btn-danger"
                                        onclick="eliminarPermanente(<?php echo $usuario['ID']; ?>, '<?php echo addslashes($nombreCompleto); ?>')">
                                    <i class="fas fa-trash-alt"></i> Borrar
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function desactivarUsuario(id, nombre) {
            confirmarAccion(
                '¿Desactivar usuario?',
                'El usuario "' + nombre + '" quedará inactivo. Podrás reactivarlo después.',
                function () {
                    window.location.href = '?desactivar=' + id + '&motivo=Desactivado por administrador';
                }
            );
        }

        function reactivarUsuario(id, nombre) {
            confirmarAccion(
                '¿Reactivar usuario?',
                'El usuario "' + nombre + '" volverá a estar activo.',
                function () {
                    window.location.href = '?activar=' + id;
                }
            );
        }

        function eliminarPermanente(id, nombre) {
            confirmarAccion(
                '⚠️ Eliminar permanentemente',
                '¿Eliminar al usuario "' + nombre + '"? Esta acción NO se puede deshacer.',
                function () {
                    confirmarAccion(
                        'Última confirmación',
                        '¿Realmente quieres eliminar a "' + nombre + '" para siempre?',
                        function () {
                            window.location.href = '?borrar_permanente=' + id;
                        }
                    );
                }
            );
        }
    </script>

    <?php renderizarAlertas(); ?>
</body>
</html>