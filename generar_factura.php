<?php
/**
 * Genera el HTML de una factura de compra
 * 
 * @param mysqli $conex
 * @param int    $venta_id
 * @param int    $usuario_id
 * @return string HTML de la factura (o cadena vacía si falla)
 */
function generarFacturaHTML($conex, $venta_id, $usuario_id) {
    // 1. Obtener datos de la venta
    $stmt = $conex->prepare("
        SELECT v.ID, v.fecha_venta, v.estado, v.metodo_pago, v.referencia_mercadopago,
            u.nombre, u.apellido, u.email
        FROM ventas v
        JOIN usuarios u ON v.ID_usuario = u.ID
        WHERE v.ID = ? AND v.ID_usuario = ?
        LIMIT 1
    ");
    $stmt->bind_param("ii", $venta_id, $usuario_id);
    $stmt->execute();
    $venta = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$venta) return '';

    // 2. Obtener detalle de la venta
    $stmt = $conex->prepare("
        SELECT dv.cantidad, dv.subtotal, e.nombre_comun, e.nombre_cientifico
        FROM detalle_ventas dv
        JOIN inventario i ON dv.ID_inventario = i.ID
        JOIN especies e ON i.ID_especie = e.ID
        WHERE dv.ID_venta = ?
    ");
    $stmt->bind_param("i", $venta_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // 3. Calcular total
    $total = 0;
    foreach ($items as $item) $total += $item['subtotal'];

    // 4. Armar HTML
    $html = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #2d8f6e; padding-bottom: 15px; }
            .header h1 { color: #1a5f4b; margin: 0; font-size: 24px; }
            .header p { color: #666; margin: 5px 0; }
            .info { margin-bottom: 20px; }
            .info-row { margin: 5px 0; }
            .info-row strong { display: inline-block; width: 130px; color: #1a3e30; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th { background: #2d8f6e; color: white; padding: 8px; text-align: left; }
            td { padding: 8px; border-bottom: 1px solid #ddd; }
            .total { text-align: right; font-size: 18px; font-weight: bold; color: #1a5f4b; margin-top: 20px; }
            .footer { text-align: center; margin-top: 40px; color: #999; font-size: 10px; border-top: 1px solid #ddd; padding-top: 10px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Verde Vida</h1>
            <p>Tu jardín digital</p>
        </div>

        <div class="info">
            <div class="info-row"><strong>Factura N°:</strong> ' . str_pad($venta['ID'], 6, '0', STR_PAD_LEFT) . '</div>
            <div class="info-row"><strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($venta['fecha_venta'])) . '</div>
            <div class="info-row"><strong>Cliente:</strong> ' . htmlspecialchars($venta['nombre'] . ' ' . $venta['apellido']) . '</div>
            <div class="info-row"><strong>Email:</strong> ' . htmlspecialchars($venta['email']) . '</div>
            <div class="info-row"><strong>Método de pago:</strong> ' . ucfirst($venta['metodo_pago']) . '</div>
            <div class="info-row"><strong>ID Pago MP:</strong> ' . htmlspecialchars($venta['referencia_mercadopago'] ?? '-') . '</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th style="text-align:center;">Cantidad</th>
                    <th style="text-align:right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($items as $item) {
        $html .= '
                <tr>
                    <td>
                        <strong>' . htmlspecialchars($item['nombre_comun']) . '</strong><br>
                        <small style="color:#999;">' . htmlspecialchars($item['nombre_cientifico']) . '</small>
                    </td>
                    <td style="text-align:center;">' . $item['cantidad'] . '</td>
                    <td style="text-align:right;">$' . number_format($item['subtotal'], 2, ',', '.') . '</td>
                </tr>';
    }

    $html .= '
            </tbody>
        </table>

        <div class="total">Total: $' . number_format($total, 2, ',', '.') . '</div>

        <div class="footer">
            <p>¡Gracias por tu compra!</p>
            <p>Verde Vida - Tu jardín digital</p>
        </div>
    </body>
    </html>';

    return $html;
}
?>