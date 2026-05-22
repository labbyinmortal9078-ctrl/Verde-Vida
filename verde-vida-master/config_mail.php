<?php
// config_mail.php - Configuración para enviar correos con Gmail

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ajusta la ruta según tu carpeta PHPMailer
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

function enviarCorreo($destinatario, $nombre_destinatario, $asunto, $cuerpo_html) {
    
    // ========== CONFIGURA AQUÍ TUS DATOS ==========
    $tu_email = "sosapatricio2025@gmail.com";        // ← TU CORREO DE GMAIL
    $tu_password = "qzoo hoxb sdlv kdim";    // ← LA CONTRASEÑA DE 16 DÍGITOS QUE TE DIO GOOGLE
    
    $mail = new PHPMailer(true);
    
    try {
        // Configuración SMTP de Gmail
        $mail->SMTPDebug = 0;                      // 0 = sin mensajes, 1 = errores, 2 = todo
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $tu_email;
        $mail->Password   = $tu_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Remitente y destinatario
        $mail->setFrom($tu_email, 'Verde Vida');
        $mail->addAddress($destinatario, $nombre_destinatario);
        
        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo_html;
        $mail->AltBody = strip_tags($cuerpo_html);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        return false;
    }
}

function generarToken($longitud = 60) {
    return bin2hex(random_bytes($longitud));
}
?>