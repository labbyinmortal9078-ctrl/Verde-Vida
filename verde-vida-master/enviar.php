<?php 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master - copia/src/Exception.php';
require 'PHPMailer-master - copia/src/PHPMailer.php';
require 'PHPMailer-master - copia/src/SMTP.php';

include("conexion.php");

$mensaje_error = '';
$mensaje_exito = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["email"])) {
    // Se crea un token único y temporal
    $token = bin2hex(random_bytes(32));
    $Ven_token = date("Y-m-d H:i:s", time() + 3600);
    $email = $_POST['email'];

    $stmt = $conex->prepare("SELECT ID FROM usuarios WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $usuario = $fila['ID'];
        
        // Actualizar token en la base de datos
        $conex->query("UPDATE usuarios SET token='$token', Ven_token='$Ven_token' WHERE ID='$usuario'");
        
        $mail = new PHPMailer(true);
        $link = "http://localhost/verde-vida-master/guardar_password.php?token=$token";
    
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sosapatricio2025@gmail.com';
            $mail->Password   = 'skem hdwt ynkp lqpb';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('sosapatricio2025@gmail.com', 'Verde Vida');
            $mail->addAddress($email, 'usuario');

            $mail->isHTML(true);
            $mail->Subject = "Restablecer contraseña - Verde Vida";
            $mail->Body = "Haz clic en el siguiente enlace para restablecer tu contraseña:<br><br>
                        <a href='$link'>$link</a><br><br>
                        Este enlace expirará en 1 hora.<br><br>
                        Si no solicitaste este cambio, ignora este mensaje.";

            $mail->send();
            $mensaje_exito = " Se ha enviado un enlace de recuperación a tu correo electrónico.";
        } catch (Exception $e) {
            $mensaje_error = " Error al enviar el correo: " . $mail->ErrorInfo;
        }
    } else {
        $mensaje_error = " No existe una cuenta con este email.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recuperar Contraseña - Verde Vida</title>
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
        .logo h1 {
            color: #2e7d32;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 20px;
        }
        h2 {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 30px;
        }
        input { 
            width: 100%; 
            padding: 12px; 
            margin: 10px 0; 
            border: 2px solid #ddd; 
            border-radius: 8px; 
            font-size: 16px;
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
        }
        .btn:hover {
            background: #1b5e20;
        }
        p { text-align: center; margin-top: 20px; }
        a { color: #2e7d32; text-decoration: none; }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="logo">
            <h1>🌿 Verde Vida</h1>
        </div>
        <h2>Recuperar Contraseña</h2>
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="error-message"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($mensaje_exito)): ?>
            <div class="success-message"><?php echo $mensaje_exito; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <input type="email" name="email" placeholder="Tu email" required>
            <button type="submit" class="btn">Enviar enlace</button>
        </form>
        <p><a href="login.php">← Volver al inicio de sesión</a></p>
    </div>
</body>
</html>