<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header("Location: main.php");
    exit();
}


$mensaje_error = '';
$mensaje_exito = '';

if (isset($_GET['error'])) {
    $errors = [
        'empty' => 'Por favor completa todos los campos obligatorios',
        'email' => 'El email no es válido',
        'exists' => 'Este email ya está registrado',
        'db' => 'Error en el servidor. Intenta nuevamente.',
    ];
    $mensaje_error = $errors[$_GET['error']] ?? 'Error desconocido';
}

if (isset($_GET['success'])) {
    $mensaje_exito = '¡Registro exitoso! Ahora puedes iniciar sesión.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Verde Vida</title>
    <style>
        :root {
            --primary-color: #2e7d32;
            --secondary-color: #4caf50;
            --light-color: #e8f5e9;
            --dark-color: #1b5e20;
            --text-color: #333;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
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
        
        .container {
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            border: 1px solid #e0e0e0;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .logo p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }
        
        .btn:hover {
            background: var(--dark-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }
        
        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .login-link a:hover {
            color: var(--dark-color);
            text-decoration: underline;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .required {
            color: #e53935;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1><span>🌿</span> Verde Vida</h1>
            <p>Crea tu cuenta para comenzar</p>
        </div>
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="error"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($mensaje_exito)): ?>
            <div class="success"><?php echo $mensaje_exito; ?></div>
        <?php endif; ?>

        <div class="form-header">
            <h2>Crear Cuenta</h2>
            <p style="color: #666;">Completa tus datos para registrarte</p>
        </div>

        <form action="procesar_registro.php" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre <span class="required">*</span></label>
                <input type="text" id="nombre" name="nombre" required placeholder="Tu nombre">
            </div>
            
            <div class="form-group">
                <label for="apellido">Apellido <span class="required">*</span></label>
                <input type="text" id="apellido" name="apellido" required placeholder="Tu apellido">
            </div>
            
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" required placeholder="tu@email.com">
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion" placeholder="Tu dirección completa">
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" placeholder="Tu número de teléfono">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña <span class="required">*</span></label>
                <input type="password" id="password" name="password" required placeholder="Mínimo 6 caracteres" minlength="6">
            </div>
            
            <button type="submit" class="btn">Crear Cuenta</button>
        </form>
        
        <div class="login-link">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
        </div>
    </div>

    <script>
        
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.style.transform = 'scale(1)';
            });
        });

    
        document.querySelector('.btn').addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        document.querySelector('.btn').addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    </script>
</body>
</html>