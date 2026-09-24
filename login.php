<?php
session_start();
include_once("conexion.php");
include_once("permisos.php");
include_once("auditoria.php");
include_once("alertas.php");

if (isset($_SESSION['usuario_id'])) {
    header("Location: main.php");
    exit();
}

$mensaje_error = '';
$mensaje_exito = '';

// Manejo de errores por GET
if (isset($_GET['error']) && !empty($_GET['error'])) {
    $error_valor = $_GET['error'];
    $errors = [
        'empty'            => 'Por favor completa todos los campos',
        'email'            => 'El email no es válido',
        'password'         => 'Contraseña incorrecta',
        'no_user'          => 'El usuario no existe. Regístrate primero',
        'no_verificado'    => 'Verifica tu email antes de iniciar sesión',
        'usuario_inactivo' => 'Tu cuenta está inactiva. Contacta al administrador.'
    ];
    $mensaje_error = $errors[$error_valor]
        ?? 'Error al iniciar sesión: ' . htmlspecialchars($error_valor);
}

if (isset($_GET['success']) && $_GET['success'] === 'registro') {
    $mensaje_exito = '¡Registro exitoso! Ahora puedes iniciar sesión.';
}

// ============================================================
// PROCESAR LOGIN
// ============================================================
if (isset($_POST['login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        header("Location: login.php?error=empty");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.php?error=email");
        exit();
    }

    // Buscar usuario
    $stmt = $conex->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        header("Location: login.php?error=no_user");
        exit();
    }

    $usuario = $resultado->fetch_assoc();   // ✅ Aquí SÍ existe $usuario

    // Verificaciones
    if (isset($usuario['email_verificado']) && $usuario['email_verificado'] == 0) {
        header("Location: login.php?error=no_verificado");
        exit();
    }

    if (isset($usuario['activo']) && $usuario['activo'] == 0) {
        header("Location: login.php?error=usuario_inactivo");
        exit();
    }

    // Verificar contraseña
    $hash = $usuario['contraseña'] ?? $usuario['password'] ?? null;
    $password_correcta = $hash ? password_verify($password, $hash) : false;

    if (!$password_correcta) {
        mostrarNotificacion('error', 'Email o contraseña incorrectos');
        header("Location: login.php");
        exit();
    }

    // ============================================================
    // LOGIN EXITOSO
    // ============================================================
    $_SESSION['usuario_id']       = $usuario['ID'];
    $_SESSION['usuario_nombre']   = $usuario['nombre'];
    $_SESSION['usuario_apellido'] = $usuario['apellido'];
    $_SESSION['usuario_email']    = $usuario['email'];
    $_SESSION['usuario_rol']      = $usuario['rol'];

    if (function_exists('getModulosPorRol')) {
        $_SESSION['modulos'] = getModulosPorRol($conex, $usuario['rol']);
    }

    // ✅ Auditoría de inicio de sesión (UNA SOLA VEZ, después de tener $usuario)
    registrarAuditoria(
        $conex,
        'Inicio de sesión',
        'usuarios',
        $usuario['ID'],
        null,
        json_encode([
            'email' => $usuario['email'],
            'rol'   => $usuario['rol']
        ])
    );

    mostrarNotificacion('exito', '¡Bienvenido ' . $usuario['nombre'] . '!');
    header("Location: main.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verde Vida - Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

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
        body::before {
            content: "🌿";
            position: absolute;
            bottom: 20px; left: 20px;
            font-size: 150px; opacity: 0.1; pointer-events: none;
        }
        body::after {
            content: "🍃";
            position: absolute;
            top: 20px; right: 20px;
            font-size: 120px; opacity: 0.1; pointer-events: none;
        }

        .login-container { width: 100%; max-width: 480px; z-index: 10; }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 32px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transition: transform 0.3s ease;
        }
        .login-card:hover { transform: translateY(-5px); }

        .logo { text-align: center; margin-bottom: 32px; }
        .logo h1 {
            font-size: 2.2rem;
            font-weight: 800;
            color: #1a3e30;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .logo-icon { font-size: 2.5rem; }
        .logo-sub { text-align: center; color: #6b8f7c; font-size: 0.9rem; margin-top: 8px; }

        .login-title { text-align: center; margin-bottom: 32px; }
        .login-title h2 { font-size: 1.8rem; font-weight: 700; color: #1a3e30; }
        .login-title p { color: #6b8f7c; font-size: 0.9rem; margin-top: 8px; }

        .input-group { margin-bottom: 20px; }
        .input-group label {
            display: block; font-size: 0.85rem; font-weight: 600;
            color: #1a3e30; margin-bottom: 8px;
        }
        .input-field {
            width: 100%; padding: 14px 16px;
            border: 2px solid #e2e8f0; border-radius: 16px;
            font-size: 1rem; font-family: 'Inter', sans-serif;
            transition: all 0.3s ease; background: white;
        }
        .input-field:focus {
            outline: none; border-color: #2d8f6e;
            box-shadow: 0 0 0 4px rgba(45, 143, 110, 0.1);
        }
        .input-field::placeholder { color: #b9cdc1; }

        .btn-login {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #2d8f6e 0%, #1a5f4b 100%);
            color: white; border: none; border-radius: 40px;
            font-size: 1rem; font-weight: 700; cursor: pointer;
            transition: all 0.3s ease; margin-top: 12px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(45, 143, 110, 0.4);
        }
        .btn-login:active { transform: translateY(0); }

        .links { text-align: center; margin-top: 24px; }
        .links a {
            color: #2d8f6e; text-decoration: none;
            font-size: 0.85rem; font-weight: 500; transition: color 0.3s;
        }
        .links a:hover { color: #1a5f4b; text-decoration: underline; }
        .divider { margin: 0 8px; color: #cbd5e1; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h1><span class="logo-icon">🌿</span> Verde Vida</h1>
                <div class="logo-sub">Tu jardín digital</div>
            </div>

            <div class="login-title">
                <h2>Bienvenido</h2>
                <p>Ingresa tus credenciales</p>
            </div>

            <form method="post" autocomplete="on">
                <div class="input-group">
                    <label>📧 Correo electrónico</label>
                    <input type="email" name="email" class="input-field"
                        placeholder="tu@email.com" required autofocus>
                </div>

                <div class="input-group">
                    <label>🔒 Contraseña</label>
                    <input type="password" name="password" class="input-field"
                        placeholder="••••••••" required>
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

    <?php if (!empty($mensaje_error)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                mostrarNotificacion('error', '<?php echo addslashes($mensaje_error); ?>');
            }, 100);
        });
    </script>
    <?php endif; ?>

    <?php if (!empty($mensaje_exito)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                mostrarNotificacion('exito', '<?php echo addslashes($mensaje_exito); ?>');
            }, 100);
        });
    </script>
    <?php endif; ?>

    <?php renderizarAlertas(); ?>
</body>
</html>