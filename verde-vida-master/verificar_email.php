<?php
session_start();
include("conexion.php");

$mensaje = '';
$tipo_mensaje = '';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    // Buscar usuario con ese token y que NO esté verificado
    $query = "SELECT ID, email, nombre FROM usuarios WHERE token_verificacion = ? AND email_verificado = 0";
    $stmt = $conex->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        
        // Actualizar como verificado
        $update = "UPDATE usuarios SET email_verificado = 1, token_verificacion = NULL, fecha_verificacion = NOW() WHERE ID = ?";
        $stmt_update = $conex->prepare($update);
        $stmt_update->bind_param("i", $usuario['ID']);
        
        if ($stmt_update->execute()) {
            $mensaje = "✅ ¡Email verificado correctamente! Ya puedes iniciar sesión.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "❌ Error al verificar tu email. Intenta nuevamente.";
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "❌ Token inválido o cuenta ya verificada. Si el token expiró, regístrate nuevamente.";
        $tipo_mensaje = "error";
    }
} else {
    $mensaje = "❌ No se proporcionó un token de verificación.";
    $tipo_mensaje = "error";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Email - Verde Vida</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0b3b2f 0%, #1c6e4a 50%, #2d936c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .card {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 2rem;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 25px 45px -12px rgba(0,0,0,0.4);
        }
        .icon { font-size: 4rem; margin-bottom: 1rem; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        h2 { margin-bottom: 1rem; color: #1a3e30; }
        p { margin-bottom: 1.5rem; color: #666; }
        .btn {
            display: inline-block;
            background: #2d8f6e;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 40px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #1a5f4b;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="card">
        <?php if ($tipo_mensaje == "success"): ?>
            <div class="icon success">✅</div>
            <h2 class="success">¡Email Verificado!</h2>
        <?php else: ?>
            <div class="icon error">❌</div>
            <h2 class="error">Error de Verificación</h2>
        <?php endif; ?>
        
        <p><?php echo $mensaje; ?></p>
        
        <?php if ($tipo_mensaje == "success"): ?>
            <a href="login.php" class="btn">Iniciar Sesión</a>
        <?php else: ?>
            <a href="registrar.php" class="btn">Volver a Registrarse</a>
        <?php endif; ?>
    </div>
</body>
</html>