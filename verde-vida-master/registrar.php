<?php
session_start();
include("conexion.php");

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master - copia/src/Exception.php';
require 'PHPMailer-master - copia/src/PHPMailer.php';
require 'PHPMailer-master - copia/src/SMTP.php';

$mensaje_error = '';
$mensaje_exito = '';

if (isset($_POST['register'])){
    if(
        isset($_POST['name']) && strlen($_POST['name']) >= 3 &&
        isset($_POST['apellido']) && strlen($_POST['apellido']) >= 3 &&
        isset($_POST['email']) && strlen($_POST['email']) >= 3 &&
        isset($_POST['direccion']) && strlen($_POST['direccion']) >= 3 &&
        isset($_POST['phone']) && strlen($_POST['phone']) >= 3 &&
        isset($_POST['password']) && strlen($_POST['password']) >= 1 &&
        isset($_POST['confirm_password']) && strlen($_POST['confirm_password']) >= 1
    ){
        $name = trim($_POST['name']);
        $apellido = trim($_POST['apellido']);
        $email = trim($_POST['email']);
        $direccion = trim($_POST['direccion']);
        $phone = trim($_POST['phone']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);  
        $fecha = date("Y-m-d H:i:s");
        
        if ($password !== $confirm_password) {
            $mensaje_error = "❌ Las contraseñas no coinciden";
        } else {
            // Verificar si el email ya existe
            $check = "SELECT ID FROM usuarios WHERE email = ?";
            $stmt = $conex->prepare($check);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $mensaje_error = "❌ Este email ya está registrado";
            } else {
                // Generar token de verificación
                $token_verificacion = bin2hex(random_bytes(32));
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $consulta = "INSERT INTO usuarios(nombre, apellido, email, contraseña, telefono, direccion, fecha_contratacion, token_verificacion, email_verificado) 
                            VALUES(?, ?, ?, ?, ?, ?, ?, ?, 0)";
                
                $stmt = $conex->prepare($consulta);
                $stmt->bind_param("ssssssss", $name, $apellido, $email, $password_hash, $phone, $direccion, $fecha, $token_verificacion);
                
                if ($stmt->execute()) {
                    // Crear enlace de verificación
                    $protocolo = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
                    $host = $_SERVER['HTTP_HOST'];
                    $ruta = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                    $enlace = $protocolo . $host . $ruta . '/verificar_email.php?token=' . $token_verificacion;
                    
                    // Configurar y enviar correo
                    $mail = new PHPMailer(true);
                    
                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'sosapatricio2025@gmail.com';
                        $mail->Password   = 'skem hdwt ynkp lqpb';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        $mail->Port       = 465;
                        
                        $mail->setFrom('sosapatricio2025@gmail.com', 'Verde Vida');
                        $mail->addAddress($email, $name);
                        
                        $mail->isHTML(true);
                        $mail->Subject = "Verifica tu cuenta - Verde Vida";
                        $mail->Body = "
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background: #2d8f6e; color: white; padding: 20px; text-align: center; }
                                .content { padding: 20px; background: #f9f9f9; }
                                .btn { display: inline-block; background: #2d8f6e; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; }
                                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>🌿 Verde Vida</h1>
                                </div>
                                <div class='content'>
                                    <h2>¡Hola $name!</h2>
                                    <p>Gracias por registrarte en Verde Vida. Para completar tu registro, por favor verifica tu dirección de email haciendo clic en el siguiente botón:</p>
                                    <p style='text-align: center;'>
                                        <a href='$enlace' class='btn'>Verificar mi cuenta</a>
                                    </p>
                                    <p>O copia y pega este enlace en tu navegador:</p>
                                    <p><small>$enlace</small></p>
                                    <p>Este enlace expirará en 24 horas.</p>
                                </div>
                                <div class='footer'>
                                    <p>© 2024 Verde Vida - Tu jardinero digital</p>
                                </div>
                            </div>
                        </body>
                        </html>
                        ";
                        
                        $mail->send();
                        $mensaje_exito = " ¡Registro exitoso! Te hemos enviado un email de verificación. Por favor revisa tu bandeja de entrada (y spam) para activar tu cuenta.";
                    } catch (Exception $e) {
                        $mensaje_exito = " Registro exitoso, pero no se pudo enviar el email de verificación. Contacta al administrador.";
                    }
                } else {
                    $mensaje_error = " Error al registrar: " . $conex->error;
                }
            }
        }
    } else {
        $mensaje_error = " Por favor, completa todos los campos correctamente";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verde Vida - Crear Cuenta</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0b3b2f 0%, #1c6e4a 50%, #2d936c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="0.8"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/><path d="M5 9.5L12 13l7-3.5"/></svg>');
            background-repeat: repeat;
            background-size: 48px;
            opacity: 0.2;
            pointer-events: none;
        }

        .card {
            max-width: 560px;
            width: 100%;
            background: rgba(255, 255, 255, 0.97);
            border-radius: 2rem;
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            z-index: 2;
        }

        .header {
            background: #0f4f38;
            padding: 1.8rem 2rem;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .header h1::before {
            content: "🌿";
            font-size: 1.9rem;
        }

        .header p {
            font-size: 0.95rem;
            margin-top: 0.4rem;
            opacity: 0.85;
        }

        .form-container {
            padding: 2rem;
        }

        .form-container h2 {
            font-size: 1.6rem;
            font-weight: 600;
            color: #1a3e30;
            margin-bottom: 0.4rem;
        }

        .form-container .sub {
            color: #4a6f5e;
            border-left: 4px solid #2d936c;
            padding-left: 0.8rem;
            margin-bottom: 1.8rem;
            font-size: 0.9rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 1rem;
            margin-bottom: 1rem;
            text-align: center;
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

        .form-grid {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .field-group label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #1f4d3c;
        }

        .required-star {
            color: #cf5a2c;
        }

        .optional-badge {
            font-size: 0.7rem;
            background: #edf4f0;
            padding: 2px 8px;
            border-radius: 30px;
            font-weight: normal;
            margin-left: 8px;
            color: #3c6e59;
        }

        .field-group input {
            padding: 0.85rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 1rem;
            font-size: 0.95rem;
            transition: all 0.2s;
            background-color: #fff;
            outline: none;
        }

        .field-group input:focus {
            border-color: #2d936c;
            box-shadow: 0 0 0 3px rgba(45, 147, 108, 0.2);
        }

        .field-group input::placeholder {
            color: #b9cdc1;
        }

        .btn-create {
            margin-top: 1.2rem;
            background: #1f5e48;
            border: none;
            padding: 0.9rem;
            width: 100%;
            border-radius: 2.5rem;
            font-size: 1rem;
            font-weight: 700;
            color: white;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-create:hover {
            background: #0e4735;
            transform: translateY(-2px);
        }

        .terms {
            font-size: 0.7rem;
            text-align: center;
            color: #6f8f81;
            margin-top: 1.8rem;
            padding-top: 1.2rem;
            border-top: 1px solid #e4ede8;
        }

        .terms a {
            color: #1f5e48;
            text-decoration: none;
        }

        .error-hint {
            font-size: 0.7rem;
            color: #d9534f;
            margin-top: 4px;
            display: none;
        }

        input.error-input {
            border-color: #e07c6c;
            background-color: #fff8f7;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <h1>Verde Vida</h1>
        <p>Crea tu cuenta para comenzar</p>
    </div>
    <div class="form-container">
        <h2>Crear Cuenta</h2>
        <div class="sub">Completa tus datos para registrarte</div>

        <?php if (!empty($mensaje_error)): ?>
            <div class="alert alert-error"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>

        <?php if (!empty($mensaje_exito)): ?>
            <div class="alert alert-success"><?php echo $mensaje_exito; ?></div>
        <?php endif; ?>

        <form id="registerForm" method="POST" action="">
            <div class="form-grid">
                <div class="field-group">
                    <label>Nombre <span class="required-star">*</span></label>
                    <input type="text" name="name" id="nombre" placeholder="Tu nombre" required>
                </div>
                
                <div class="field-group">
                    <label>Apellido <span class="required-star">*</span></label>
                    <input type="text" name="apellido" id="apellido" placeholder="Tu apellido" required>
                </div>
                
                <div class="field-group">
                    <label>Email <span class="required-star">*</span></label>
                    <input type="email" name="email" id="email" placeholder="tu@email.com" required>
                </div>
                
                <div class="field-group">
                    <label>Dirección <span class="optional-badge"></span></label>
                    <input type="text" name="direccion" id="direccion" placeholder="Tu dirección completa">
                </div>
                
                <div class="field-group">
                    <label>Teléfono <span class="optional-badge"></span></label>
                    <input type="tel" name="phone" id="telefono" placeholder="Tu número de teléfono">
                </div>

                <div class="field-group">
                    <label>Contraseña <span class="required-star">*</span></label>
                    <input type="password" name="password" id="password" placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="field-group">
                    <label>Confirmar Contraseña <span class="required-star">*</span></label>
                    <input type="password" name="confirm_password" id="confirmPassword" placeholder="Repite tu contraseña" required>
                    <div id="confirmError" class="error-hint">✖ Las contraseñas no coinciden</div>
                </div>
            </div>

            <button type="submit" name="register" class="btn-create">Crear Cuenta</button>
            <div class="terms">
                Al registrarte aceptas nuestros <a href="#">Términos de uso</a> y <a href="#">Política de privacidad</a>.
            </div>
        </form>
    </div>
</div>

<script>
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirmPassword');
    const confirmErrorDiv = document.getElementById('confirmError');

    function validatePasswordMatch() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        if (confirm === "") {
            confirmErrorDiv.style.display = 'none';
            confirmInput.classList.remove('error-input');
            return false;
        }
        
        if (password !== confirm) {
            confirmErrorDiv.style.display = 'block';
            confirmInput.classList.add('error-input');
            return false;
        } else {
            confirmErrorDiv.style.display = 'none';
            confirmInput.classList.remove('error-input');
            return true;
        }
    }

    passwordInput.addEventListener('input', validatePasswordMatch);
    confirmInput.addEventListener('input', validatePasswordMatch);
    
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        if (password !== confirm) {
            e.preventDefault();
            confirmErrorDiv.style.display = 'block';
            confirmInput.classList.add('error-input');
            alert(' Las contraseñas no coinciden. Por favor, verifícalas.');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert(' La contraseña debe tener al menos 6 caracteres.');
            passwordInput.focus();
            return false;
        }
    });
</script>
</body>
</html>