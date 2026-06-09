<?php
session_start();
include("conexion.php");
include("permisos.php"); 

// Verificar administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'administrador') {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$mensaje = '';

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nuevo_rol = $_POST['rol'];
    
    // Evitar que un administrador se quite su propio rol
    if ($id == $_SESSION['usuario_id'] && $nuevo_rol != 'administrador') {
        header("Location: admin_usuarios.php?error=no_puedes_cambiarte");
        exit();
    }
    
    // Validar que el rol exista en la lista de roles permitidos
    $roles_permitidos = getAllRoles();
    if (!in_array($nuevo_rol, $roles_permitidos)) {
        header("Location: admin_usuarios.php?error=1");
        exit();
    }
    
    // Usar prepared statement por seguridad
    $stmt = $conex->prepare("UPDATE usuarios SET rol = ? WHERE ID = ?");
    $stmt->bind_param("si", $nuevo_rol, $id);
    
    if ($stmt->execute()) {
        header("Location: admin_usuarios.php?exito=1&rol=" . urlencode($nuevo_rol));
        exit();
    } else {
        header("Location: admin_usuarios.php?error=1");
        exit();
    }
}

// Obtener datos del usuario con prepared statement
$stmt = $conex->prepare("SELECT * FROM usuarios WHERE ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    header("Location: admin_usuarios.php");
    exit();
}

// Obtener lista de roles desde permisos.php
$roles_disponibles = getAllRoles();

// Mapeo de roles a iconos y nombres mostrables
$iconos_roles = [
    'usuario' => '👤',
    'jardinero' => '🌱',
    'empleado' => '📋',
    'administrador' => '👑'
];

$nombres_roles = [
    'usuario' => 'Usuario',
    'jardinero' => 'Jardinero',
    'empleado' => 'Empleado',
    'administrador' => 'Administrador'
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cambiar Rol - Verde Vida</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial; background: #e8f5e9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 20px; width: 450px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        select, button { width: 100%; padding: 10px; margin: 10px 0; border-radius: 10px; border: 1px solid #ccc; font-size: 16px; }
        button { background: #2d8f6e; color: white; font-weight: bold; cursor: pointer; }
        button:hover { background: #1a5f4b; }
        .info { background: #e2e3e5; padding: 8px; border-radius: 8px; margin: 10px 0; }
        a { color: #2d8f6e; text-decoration: none; }
        select:disabled { opacity: 0.6; cursor: not-allowed; }
        button:disabled { opacity: 0.6; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🌿 Verde Vida</h1>
        <h3>Cambiar Rol de Usuario</h3>

        <div class="info">
            <p><strong>Usuario:</strong> <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
            <p><strong>Rol actual:</strong> <strong style="color:#2d8f6e">
                <?php echo $iconos_roles[$usuario['rol']] ?? '👤'; ?> <?php echo $nombres_roles[$usuario['rol']] ?? $usuario['rol']; ?>
            </strong></p>
        </div>

        <form method="POST">
            <select name="rol" <?php echo ($id == $_SESSION['usuario_id']) ? 'disabled' : ''; ?>>
                <?php foreach ($roles_disponibles as $rol): ?>
                    <?php $icono = $iconos_roles[$rol] ?? '👤'; ?>
                    <?php $nombre = $nombres_roles[$rol] ?? $rol; ?>
                    <option value="<?php echo $rol; ?>" <?php echo $usuario['rol'] == $rol ? 'selected' : ''; ?>>
                        <?php echo $icono; ?> <?php echo $nombre; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <?php if ($id == $_SESSION['usuario_id']): ?>
                <p style="color: #dc3545; margin-top: 5px; font-size: 0.9rem;">
                        No puedes cambiar tu propio rol
                </p>
            <?php endif; ?>
            
            <button type="submit" <?php echo ($id == $_SESSION['usuario_id']) ? 'disabled' : ''; ?>>
                Guardar Cambios
            </button>
        </form>

        <br>
        <a href="admin_usuarios.php">← Volver a Usuarios</a>
    </div>
</body>
</html>