<?php
/**
 * Webhook: recibe notificaciones de Mercado Pago
 * Documentación: https://www.mercadopago.com.ar/developers/es/docs/your-integrations/notifications/webhooks
 */
require_once 'vendor/autoload.php';
include_once("conexion.php");
include_once("carrito_funciones.php");
include_once("auditoria.php");
include_once("config_mp.php");

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;

// Log para debug
$log = "[" . date('Y-m-d H:i:s') . "] Webhook recibido\n";
$log .= "GET: " . json_encode($_GET) . "\n";
$log .= "POST: " . file_get_contents('php://input') . "\n";
file_put_contents('webhook_mp.log', $log . "\n", FILE_APPEND);

MercadoPagoConfig::setAccessToken(MP_ACCESS_TOKEN);

// Extraer payment_id (puede venir en GET o en POST)
$payment_id = $_GET['data_id'] ?? $_GET['id'] ?? null;

if (!$payment_id) {
    $body = json_decode(file_get_contents('php://input'), true);
    $payment_id = $body['data']['id'] ?? null;
}

if (!$payment_id) {
    http_response_code(200);
    echo json_encode(['ok' => true, 'msg' => 'Sin payment_id']);
    exit();
}

try {
    $client = new PaymentClient();
    $payment = $client->get($payment_id);

    if ($payment->status === 'approved') {
        // Extraer usuario desde external_reference
        $ext_ref = $payment->external_reference;
        $partes = explode('_', $ext_ref);
        $usuario_id = isset($partes[1]) ? (int)$partes[1] : null;

        if ($usuario_id) {
            // Verificar si la venta ya fue creada (evitar duplicados)
            $stmt = $conex->prepare("SELECT ID FROM ventas WHERE referencia_mercadopago = ? LIMIT 1");
            $stmt->bind_param("s", $payment_id);
            $stmt->execute();
            $existe = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if (!$existe) {
                $resultado = crearVentaDesdeCarrito($conex, $usuario_id, (string) $payment_id);

                if (isset($resultado['exito'])) {
                    descontarStock($conex, $resultado['venta_id']);
                    marcarCarritoPagado($conex, $usuario_id);

                    registrarAuditoria(
                        $conex,
                        'Compra online (webhook)',
                        'ventas',
                        $resultado['venta_id'],
                        null,
                        json_encode([
                            'total'   => $resultado['total'],
                            'payment' => $payment_id
                        ])
                    );
                }
            }
        }
    }

    http_response_code(200);
    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    file_put_contents('webhook_mp.log', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(200); // Siempre 200 para que MP no reintente
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}