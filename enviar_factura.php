<?php
require_once 'vendor/autoload.php';
require_once 'generar_factura.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Genera el PDF y lo envía por email al cliente
 */
function enviarFacturaPorEmail($conex, $venta_id, $usuario_id) {
    // 1. Obtener datos del usuario
    $stmt = $conex->prepare("SELECT email, nombre, apellido FROM usuarios WHERE ID = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$usuario || empty($usuario['email'])) {
        return ['exito' => false, 'mensaje' => 'Usuario sin email'];
    }

    // 2. Generar HTML de la factura
    $html = generarFacturaHTML($conex, $venta_id, $usuario_id);
    if (empty($html)) {
        return ['exito' => false, 'mensaje' => 'No se pudo generar la factura'];
    }

    // 3. Convertir HTML a PDF con Dompdf
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $pdf_content = $dompdf->output();

    // 4. Guardar PDF temporalmente para adjuntar
    $nombre_pdf = 'Factura_VerdeVida_' . str_pad($venta_id, 6, '0', STR_PAD_LEFT) . '.pdf';
    $ruta_temp  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $nombre_pdf;
    file_put_contents($ruta_temp, $pdf_content);

    // 5. Enviar por email con PHPMailer
    // ⚠️ AQUÍ van TUS datos SMTP directamente
    $tu_email    = "sosapatricio2025@gmail.com";
    $tu_password = "qzoo hoxb sdlv kdim";

    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug  = 0;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $tu_email;
        $mail->Password   = $tu_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($tu_email, 'Verde Vida');
        $mail->addAddress($usuario['email'], $usuario['nombre'] . ' ' . $usuario['apellido']);
        $mail->addReplyTo($tu_email, 'Verde Vida');

        $mail->isHTML(true);
        $mail->Subject = '🌿 Tu factura de compra - Verde Vida #' . str_pad($venta_id, 6, '0', STR_PAD_LEFT);
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color:#1a5f4b;'>¡Gracias por tu compra, " . htmlspecialchars($usuario['nombre']) . "!</h2>
                <p>Adjuntamos la factura de tu pedido <strong>N° " . str_pad($venta_id, 6, '0', STR_PAD_LEFT) . "</strong>.</p>
                <p>Si tenés alguna consulta, respondé a este correo.</p>
                <br>
                <p style='color:#666;'>Verde Vida - Tu jardín digital 🌱</p>
            </div>
        ";
        $mail->AltBody = "¡Gracias por tu compra! Adjuntamos la factura N° " . str_pad($venta_id, 6, '0', STR_PAD_LEFT) . ".";

        // Adjuntar el PDF desde archivo temporal
        $mail->addAttachment($ruta_temp);

        $mail->send();

        // Borrar PDF temporal
        if (file_exists($ruta_temp)) {
            unlink($ruta_temp);
        }

        return ['exito' => true, 'mensaje' => 'Factura enviada a ' . $usuario['email']];

    } catch (Exception $e) {
        // Borrar PDF temporal en caso de error
        if (file_exists($ruta_temp)) {
            unlink($ruta_temp);
        }
        error_log("Error al enviar factura: " . $mail->ErrorInfo);
        return ['exito' => false, 'mensaje' => 'Error: ' . $mail->ErrorInfo];
    }
}
?>