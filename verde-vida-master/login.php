<?php
session_start();
include("conexion.php");

if (isset($_SESSION['usuario_id'])) {
    header("Location: main.php");
    exit();
}

$mensaje_error = '';
$mensaje_exito = '';

//  Manejo de errores más seguro
if (isset($_GET['error']) && !empty($_GET['error'])) {
    $error_valor = $_GET['error'];
    $errors = [
        'empty' => ' Por favor completa todos los campos',
        'email' => ' El email no es válido',
        'password' => ' Contraseña incorrecta',
        'no_user' => ' El usuario no existe. Regístrate primero',
    ];
    
    if (isset($errors[$error_valor])) {
        $mensaje_error = $errors[$error_valor];
    } else {
        $mensaje_error = ' Error al iniciar sesión: ' . htmlspecialchars($error_valor);
    }
}

if (isset($_GET['success']) && $_GET['success'] == 'registro') {
    $mensaje_exito = ' ¡Registro exitoso! Ahora puedes iniciar sesión.';
}

// Procesar el formulario de login
if (isset($_POST['login'])) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    if (empty($email) || empty($password)) {
        header("Location: login.php?error=empty");
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.php?error=email");
        exit();
    }
    
    $consulta = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conex->prepare($consulta);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0){
        $usuario = $resultado->fetch_assoc();
        
        //  Verificar qué nombre tiene la columna de contraseña
        if (isset($usuario['contraseña'])) {
            $password_correcta = password_verify($password, $usuario['contraseña']);
        } elseif (isset($usuario['password'])) {
            $password_correcta = password_verify($password, $usuario['password']);
        } else {
            $password_correcta = false;
        }
        
        if ($password_correcta) {
            $_SESSION['usuario_id'] = $usuario['ID'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_apellido'] = $usuario['apellido']; 
            $_SESSION['usuario_email'] = $usuario['email'];
            header("Location: main.php");
            exit();
        } else {
            header("Location: login.php?error=password");
            exit();
        }
    } else {
        header("Location: login.php?error=no_user");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Verde Vida</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
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
        .logo { text-align: center; margin-bottom: 20px; }
        .logo h1 { color: #2e7d32; font-size: 2rem; display: flex; align-items: center; justify-content: center; gap: 10px; }
        h2 { color: #2e7d32; text-align: center; margin-bottom: 30px; font-size: 1.5rem; }
        input {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        input:focus { outline: none; border-color: #4caf50; }
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
        }
        .btn:hover { background: #1b5e20; transform: translateY(-2px); }
        p { text-align: center; margin-top: 20px; color: #666; }
        a { color: #2e7d32; text-decoration: none; font-weight: 600; }
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
        <h2>Iniciar Sesión</h2>
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="error-message"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($mensaje_exito)): ?>
            <div class="success-message"><?php echo $mensaje_exito; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="submit" name="login" value="Iniciar Sesión" class="btn">
        </form>
        
        <p>¿No tienes cuenta? <a href="index.php">Regístrate aquí</a></p>
        <p>¿Olvidaste tu contraseña? <a href="enviar.php">Restablecer contraseña</a></p>
    </div>
</body>
</html>