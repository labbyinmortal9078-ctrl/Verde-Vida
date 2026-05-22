<?php
session_start();
include("conexion.php");

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
    
    $sql = "UPDATE usuarios SET rol = '$nuevo_rol' WHERE ID = $id";
    
    if ($conex->query($sql)) {
        // Redirigir con mensaje de éxito
        header("Location: admin_usuarios.php?exito=1&rol=" . urlencode($nuevo_rol));
        exit();
    } else {
        header("Location: admin_usuarios.php?error=1");
        exit();
    }
}

// Obtener datos del usuario
$result = $conex->query("SELECT * FROM usuarios WHERE ID = $id");
$usuario = $result->fetch_assoc();

if (!$usuario) {
    header("Location: admin_usuarios.php");
    exit();
}
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
    </style>
</head>
<body>
    <div class="card">
        <h1>🌿 Verde Vida</h1>
        <h3>Cambiar Rol de Usuario</h3>

        <div class="info">
            <p><strong>Usuario:</strong> <?php echo $usuario['nombre'] . ' ' . $usuario['apellido']; ?></p>
            <p><strong>Email:</strong> <?php echo $usuario['email']; ?></p>
            <p><strong>Rol actual:</strong> <strong style="color:#2d8f6e"><?php echo $usuario['rol']; ?></strong></p>
        </div>

        <form method="POST">
            <select name="rol">
                <option value="usuario" <?php echo $usuario['rol'] == 'usuario' ? 'selected' : ''; ?>>👤 Usuario</option>
                <option value="jardinero" <?php echo $usuario['rol'] == 'jardinero' ? 'selected' : ''; ?>>🌱 Jardinero</option>
                <option value="empleado" <?php echo $usuario['rol'] == 'empleado' ? 'selected' : ''; ?>>📋 Empleado</option>
                <option value="administrador" <?php echo $usuario['rol'] == 'administrador' ? 'selected' : ''; ?>>👑 Administrador</option>
            </select>
            <button type="submit">Guardar Cambios</button>
        </form>

        <br>
        <a href="admin_usuarios.php">← Volver a Usuarios</a>
    </div>
</body>
</html>