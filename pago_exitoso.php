<?php
session_start();
require_once 'vendor/autoload.php';
include_once("conexion.php");
include_once("alertas.php");
include_once("carrito_funciones.php");
include_once("config_mp.php");
include_once("auditoria.php");

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;


function log_debug($msg) {
    file_put_contents('debug_audit.txt', date('Y-m-d H:i:s') . ' - ' . $msg . "\n", FILE_APPEND);
}

log_debug('=== INICIO pago_exitoso.php ===');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$payment_id = $_GET['payment_id'] ?? $_GET['collection_id'] ?? null;
$status     = $_GET['status'] ?? $_GET['collection_status'] ?? null;

log_debug("usuario_id=$usuario_id | payment_id=" . ($payment_id ?? 'NULL'));

$venta_creada = false;
$total_pagado = 0;
$resultado    = null;

// ============================================================
// VERIFICAR PAGO CON MP
// ============================================================
if ($payment_id) {
    MercadoPagoConfig::setAccessToken(trim(MP_ACCESS_TOKEN));
    MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

    try {
        $client = new PaymentClient();
        $payment = $client->get($payment_id);

        log_debug("Payment status: " . $payment->status);

        if ($payment->status === 'approved') {
            $ext_ref  = $payment->external_reference ?? '';
            $partes   = explode('_', $ext_ref);
            $user_ref = isset($partes[1]) ? (int) $partes[1] : $usuario_id;

            if ($user_ref === $usuario_id) {
                $resultado = crearVentaDesdeCarrito($conex, $usuario_id, (string) $payment_id);

                log_debug("crearVentaDesdeCarrito (MP): " . print_r($resultado, true));

                if (isset($resultado['exito'])) {
                    descontarStock($conex, $resultado['venta_id']);
                    marcarCarritoPagado($conex, $usuario_id);

                    // Registrar auditoría
                    $detalle = json_encode([
                        'Venta N°'   => str_pad($resultado['venta_id'], 6, '0', STR_PAD_LEFT),
                        'Total'      => '$' . number_format($resultado['total'], 2),
                        'Método'     => 'Mercado Pago',
                        'ID Pago MP' => $payment_id
                    ], JSON_UNESCAPED_UNICODE);

                    $audit_ok = registrarAuditoria(
                        $conex,
                        'Compra realizada',
                        'ventas',
                        $resultado['venta_id'],
                        null,
                        $detalle
                    );

                    log_debug("Auditoría MP: " . ($audit_ok ? 'OK' : 'FALLÓ') . " | detalle=$detalle");

                    $venta_creada = true;
                    $total_pagado = $resultado['total'];
                }
            }
        }
    } catch (Exception $e) {
        log_debug("Error MP: " . $e->getMessage());
    }
}


if (!$venta_creada) {
    log_debug("Entrando en FALLBACK (no se creó venta con payment_id)");
    
    $items = obtenerItemsCarrito($conex, $usuario_id);
    log_debug("Items en carrito: " . count($items));

    if (!empty($items)) {
        $ref_temp = 'sandbox_' . time();
        $resultado = crearVentaDesdeCarrito($conex, $usuario_id, $ref_temp);

        log_debug("crearVentaDesdeCarrito (sandbox): " . print_r($resultado, true));

        if (isset($resultado['exito'])) {
            descontarStock($conex, $resultado['venta_id']);
            marcarCarritoPagado($conex, $usuario_id);

            // Registrar auditoría
            $detalle = json_encode([
                'Venta N°' => str_pad($resultado['venta_id'], 6, '0', STR_PAD_LEFT),
                'Total'    => '$' . number_format($resultado['total'], 2),
                'Método'   => 'Mercado Pago (sandbox)'
            ], JSON_UNESCAPED_UNICODE);

            $audit_ok = registrarAuditoria(
                $conex,
                'Compra realizada',
                'ventas',
                $resultado['venta_id'],
                null,
                $detalle
            );

            log_debug("Auditoría sandbox: " . ($audit_ok ? 'OK' : 'FALLÓ') . " | detalle=$detalle");

            $venta_creada = true;
            $total_pagado = $resultado['total'];
        }
    } else {
        log_debug("Carrito vacío, no se crea venta");
    }
}

log_debug("venta_creada=" . ($venta_creada ? 'SI' : 'NO') . " | total=$total_pagado");

// ============================================================
// ENVIAR FACTURA (opcional)
// ============================================================
$factura_enviada = false;
if ($venta_creada && isset($resultado['venta_id'])) {
    try {
        require_once 'enviar_factura.php';
        $res_email = enviarFacturaPorEmail($conex, $resultado['venta_id'], $usuario_id);
        if ($res_email['exito']) {
            $factura_enviada = true;
            log_debug("Factura enviada OK");
        } else {
            log_debug("Factura falló: " . $res_email['mensaje']);
        }
    } catch (Exception $e) {
        log_debug("Excepción enviando factura: " . $e->getMessage());
    }
}

$total_items_carrito = contarItemsCarrito($conex, $usuario_id);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>¡Pago Exitoso! - Verde Vida</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .card { background: white; border-radius: 24px; padding: 3rem; max-width: 500px; width: 100%; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .icon { width: 100px; height: 100px; background: linear-gradient(135deg, #28a745, #20c997); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 3rem; color: white; }
        h1 { color: #155724; margin-bottom: 0.5rem; }
        .subtitle { color: #666; margin-bottom: 2rem; }
        .info { background: #f8f9fa; border-radius: 12px; padding: 1rem; margin-bottom: 2rem; text-align: left; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.9rem; }
        .info-row span:first-child { color: #666; }
        .info-row span:last-child { font-weight: 600; color: #1a3e30; }
        .email-notice { background: #e7f3ff; border-left: 4px solid #2d8f6e; padding: 10px 14px; margin-bottom: 1rem; border-radius: 8px; font-size: 0.85rem; color: #1a5f4b; text-align: left; }
        .buttons { display: flex; gap: 1rem; flex-wrap: wrap; }
        .btn { flex: 1; padding: 12px 24px; border: none; border-radius: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #2d8f6e 0%, #1a5f4b 100%); color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon"><i class="fas fa-check"></i></div>
        <h1>¡Pago exitoso!</h1>
        <p class="subtitle">Tu compra se ha procesado correctamente</p>

        <?php if ($venta_creada): ?>
            <div class="info">
                <?php if ($payment_id): ?>
                <div class="info-row"><span>ID de pago:</span><span><?php echo htmlspecialchars($payment_id); ?></span></div>
                <?php endif; ?>
                <div class="info-row"><span>Total:</span><span>$<?php echo number_format($total_pagado, 2); ?></span></div>
                <div class="info-row"><span>Método:</span><span>Mercado Pago</span></div>
            </div>

            <?php if ($factura_enviada): ?>
                <div class="email-notice">
                    <i class="fas fa-envelope"></i> Te enviamos la factura a tu correo electrónico.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="info">
                <p style="text-align:center; color:#666; font-size:0.9rem;">
                    Estamos verificando tu pago. En unos minutos aparecerá en tu historial.
                </p>
            </div>
        <?php endif; ?>

        <div class="buttons">
            <a href="ver_plantas.php" class="btn btn-primary"><i class="fas fa-seedling"></i> Seguir comprando</a>
            <a href="main.php" class="btn btn-secondary"><i class="fas fa-home"></i> Ir al inicio</a>
        </div>
    </div>
    <?php renderizarAlertas(); ?>
</body>
</html>