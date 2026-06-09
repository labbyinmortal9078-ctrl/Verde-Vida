<?php
session_start();
include("conexion.php");

if (isset($_SESSION['usuario_id'])) {
    header("Location: main.php");
    exit();
}

$mensaje_error = '';
$mensaje_exito = '';

// Manejo de errores más seguro
if (isset($_GET['error']) && !empty($_GET['error'])) {
    $error_valor = $_GET['error'];
    $errors = [
        'empty' => 'Por favor completa todos los campos',
        'email' => 'El email no es válido',
        'password' => 'Contraseña incorrecta',
        'no_user' => 'El usuario no existe. Regístrate primero',
        'no_verificado' => 'Verifica tu email antes de iniciar sesión',
        'usuario_inactivo' => 'Tu cuenta está inactiva. Contacta al administrador.' 
    ];
    
    if (isset($errors[$error_valor])) {
        $mensaje_error = $errors[$error_valor];
    } else {
        $mensaje_error = 'Error al iniciar sesión: ' . htmlspecialchars($error_valor);
    }
}

if (isset($_GET['success']) && $_GET['success'] == 'registro') {
    $mensaje_exito = '¡Registro exitoso! Ahora puedes iniciar sesión.';
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
        
        if (isset($usuario['email_verificado']) && $usuario['email_verificado'] == 0) {
            header("Location: login.php?error=no_verificado");
            exit();
        }
        
        if(isset($usuario['activo']) && $usuario['activo'] == 0) {
            header("Location: login.php?error=usuario_inactivo");
            exit();
        }
        
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
            $_SESSION['usuario_rol'] = $usuario['rol'];
            include("permisos.php");
            $_SESSION['modulos'] = getModulosPorRol($conex, $usuario['rol']);   
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
    <title>Verde Vida - Iniciar Sesión</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0b3b2f 0%, #1c6e4a 50%, #2d936c 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }

        /* Fondo decorativo */
        body::before {
            content: "🌿";
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 150px;
            opacity: 0.1;
            pointer-events: none;
        }

        body::after {
            content: "🍃";
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 120px;
            opacity: 0.1;
            pointer-events: none;
        }

        /* Contenedor principal */
        .login-container {
            width: 100%;
            max-width: 480px;
            z-index: 10;
        }

        /* Tarjeta de login */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 32px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        /* Logo */
        .logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo h1 {
            font-size: 2.2rem;
            font-weight: 800;
            color: #1a3e30;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .logo-icon {
            font-size: 2.5rem;
        }

        .logo-sub {
            text-align: center;
            color: #6b8f7c;
            font-size: 0.9rem;
            margin-top: 8px;
        }

        /* Título */
        .login-title {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-title h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a3e30;
        }

        .login-title p {
            color: #6b8f7c;
            font-size: 0.9rem;
            margin-top: 8px;
        }

        /* Campos de formulario */
        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #1a3e30;
            margin-bottom: 8px;
        }

        .input-field {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            background: white;
        }

        .input-field:focus {
            outline: none;
            border-color: #2d8f6e;
            box-shadow: 0 0 0 4px rgba(45, 143, 110, 0.1);
        }

        .input-field::placeholder {
            color: #b9cdc1;
        }

        /* Botón */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2d8f6e 0%, #1a5f4b 100%);
            color: white;
            border: none;
            border-radius: 40px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 12px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(45, 143, 110, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Enlaces */
        .links {
            text-align: center;
            margin-top: 24px;
        }

        .links a {
            color: #2d8f6e;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: color 0.3s;
        }

        .links a:hover {
            color: #1a5f4b;
            text-decoration: underline;
        }

        .divider {
            margin: 0 8px;
            color: #cbd5e1;
        }

        /* ========== ALERTAS MODERNAS (GLASSMORPHISM) ========== */
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            left: 20px;
            max-width: 400px;
            margin: 0 auto;
            z-index: 1000;
        }

        @media (min-width: 768px) {
            .alert-container {
                right: 30px;
                left: auto;
                margin: 0;
            }
        }

        .alert {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 16px 20px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(255, 255, 255, 0.3);
            animation: slideInRight 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .alert:hover {
            transform: translateX(-5px);
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.15);
        }

        .alert-success {
            border-left: 4px solid #28a745;
        }
        .alert-success .alert-icon {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .alert-error {
            border-left: 4px solid #dc3545;
        }
        .alert-error .alert-icon {
            background: linear-gradient(135deg, #dc3545, #e83e8c);
        }

        .alert-warning {
            border-left: 4px solid #ffc107;
        }
        .alert-warning .alert-icon {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .alert-info {
            border-left: 4px solid #17a2b8;
        }
        .alert-info .alert-icon {
            background: linear-gradient(135deg, #17a2b8, #6f42c1);
        }

        .alert-icon {
            width: 40px;
            height: 40px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            flex-shrink: 0;
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 4px;
        }

        .alert-success .alert-title { color: #28a745; }
        .alert-error .alert-title { color: #dc3545; }
        .alert-warning .alert-title { color: #ffc107; }
        .alert-info .alert-title { color: #17a2b8; }

        .alert-message {
            font-size: 0.8rem;
            color: #4a5568;
            line-height: 1.4;
        }

        .alert-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #a0aec0;
            transition: color 0.2s;
            width: 28px;
            height: 28px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .alert-close:hover {
            color: #4a5568;
            background: #f1f5f9;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }

        .alert.fade-out {
            animation: slideOutRight 0.3s ease forwards;
        }
    </style>
</head>
<body>
    <!-- Contenedor de alertas -->
    <div class="alert-container" id="alertContainer"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h1>
                    <span class="logo-icon">🌿</span>
                    Verde Vida
                </h1>
                <div class="logo-sub">Tu jardín digital</div>
            </div>

            <div class="login-title">
                <h2>Bienvenido</h2>
                <p>Ingresa tus credenciales</p>
            </div>

            <form method="post">
                <div class="input-group">
                    <label>📧 Correo electrónico</label>
                    <input type="email" name="email" class="input-field" placeholder="tu@email.com" required>
                </div>

                <div class="input-group">
                    <label>🔒 Contraseña</label>
                    <input type="password" name="password" class="input-field" placeholder="••••••••" required>
                </div>

                <button type="submit" name="login" class="btn-login">
                    Iniciar Sesión
                </button>
            </form>

            <div class="links">
                <a href="registrar.php">📝 Crear cuenta</a>
                <span class="divider">•</span>
                <a href="enviar.php">🔑 ¿Olvidaste tu contraseña?</a>
            </div>
        </div>
    </div>

    <script>
        // Configuración de alertas
        const alertTitles = {
            'success': '¡Excelente!',
            'error': '¡Oops!',
            'warning': 'Atención',
            'info': 'Información'
        };

        const alertIcons = {
            'success': '✓',
            'error': '✗',
            'warning': '!',
            'info': 'ℹ'
        };

        // Función para mostrar alerta
        function showAlert(type, message) {
            const container = document.getElementById('alertContainer');
            
            // Crear elemento alerta
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            
            alert.innerHTML = `
                <div class="alert-icon">${alertIcons[type] || 'ℹ️'}</div>
                <div class="alert-content">
                    <div class="alert-title">${alertTitles[type] || 'Notificación'}</div>
                    <div class="alert-message">${message}</div>
                </div>
                <button class="alert-close" onclick="closeAlert(this)">×</button>
            `;
            
            // Cerrar al hacer clic en la alerta (excepto en el botón)
            alert.addEventListener('click', function(e) {
                if (e.target !== alert.querySelector('.alert-close')) {
                    closeAlert(this);
                }
            });
            
            container.appendChild(alert);
            
            // Auto-cerrar después de 5 segundos
            setTimeout(() => {
                if (alert.parentElement) closeAlert(alert);
            }, 5000);
        }

        // Función para cerrar alerta
        function closeAlert(element) {
            const alert = element.classList ? element : element.parentElement;
            alert.classList.add('fade-out');
            setTimeout(() => alert.remove(), 300);
        }

        // Mostrar alertas si hay mensajes
        <?php if (!empty($mensaje_error)): ?>
            showAlert('error', '<?php echo addslashes($mensaje_error); ?>');
        <?php endif; ?>

        <?php if (!empty($mensaje_exito)): ?>
            showAlert('success', '<?php echo addslashes($mensaje_exito); ?>');
        <?php endif; ?>
    </script>
</body>
</html>