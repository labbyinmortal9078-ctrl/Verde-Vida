<?php
session_start();
include("conexion.php");

$mensaje_error = '';
$mensaje_exito = '';
$token_valido = false;
$token = '';

// Verificar el token recibido por GET
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $conex->prepare("SELECT ID, Ven_token FROM usuarios WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $Ven = $fila["Ven_token"];
        
        // Verificar si el token no ha expirado
        if (strtotime($Ven) > time()) {
            $token_valido = true;
        } else {
            $mensaje_error = " El enlace ha expirado. Solicita uno nuevo.";
        }
    } else {
        $mensaje_error = " Token inválido.";
    }
} else {
    $mensaje_error = " No se recibió el token.";
}

// Procesar el cambio de contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["restablecer"])) {
    $password = trim($_POST["nueva_contraseña"]);
    $verificar = trim($_POST["verificar_contraseña"]);
    $token = trim($_POST["token"]);
    
    if (empty($password) || empty($verificar)) {
        $mensaje_error = " Por favor completa todos los campos.";
    } elseif (strlen($password) < 6) {
        $mensaje_error = " La contraseña debe tener al menos 6 caracteres.";
    } elseif ($password !== $verificar) {
        $mensaje_error = " Las contraseñas no coinciden.";
    } else {
        // Verificar el token nuevamente antes de actualizar
        $stmt = $conex->prepare("SELECT ID, Ven_token FROM usuarios WHERE token = ? AND Ven_token > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conex->prepare("UPDATE usuarios SET contraseña = ?, token = NULL, Ven_token = NULL WHERE token = ?");
            $stmt->bind_param("ss", $hash, $token);
            
            if ($stmt->execute()) {
                $mensaje_exito = " ¡Contraseña actualizada correctamente! Ya puedes <a href='login.php'>iniciar sesión</a>.";
            } else {
                $mensaje_error = " Error al actualizar la contraseña: " . $stmt->error;
            }
        } else {
            $mensaje_error = " Token inválido o expirado.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Restablecer Contraseña - Verde Vida</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8f5e9 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .form-container {
            max-width: 450px;
            width: 100%;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo h1 {
            color: #2e7d32;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        h2 {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5rem;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        input:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        .btn {
            background: #4caf50;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #1b5e20;
            transform: translateY(-2px);
        }
        p {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="logo">
            <h1>🌿 Verde Vida</h1>
        </div>
        <h2>Restablecer Contraseña</h2>
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="error-message"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($mensaje_exito)): ?>
            <div class="success-message"><?php echo $mensaje_exito; ?></div>
        <?php endif; ?>
        
        <?php if ($token_valido && empty($mensaje_exito)): ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="password" name="nueva_contraseña" placeholder="Nueva contraseña" required minlength="6">
                <input type="password" name="verificar_contraseña" placeholder="Confirmar contraseña" required>
                <button type="submit" name="restablecer" class="btn">Restablecer Contraseña</button>
            </form>
        <?php elseif (!$token_valido && empty($mensaje_exito)): ?>
            <p style="text-align: center; color: #721c24;">No se pudo verificar el enlace.</p>
            <p><a href="enviar.php">Solicitar nuevo enlace</a></p>
        <?php endif; ?>
        
        <p><a href="login.php">← Volver al inicio de sesión</a></p>
    </div>
</body>
</html>