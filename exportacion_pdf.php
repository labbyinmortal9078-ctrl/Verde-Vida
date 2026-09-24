<?php
session_start();
include_once("conexion.php");
include_once("permisos.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Incluir dompdf
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// ========== CONSULTAS ==========
$query_plantas_mes = "SELECT DATE(hora) as fecha, COUNT(*) as total 
                        FROM especies 
                        WHERE hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                        GROUP BY DATE(hora) 
                        ORDER BY fecha ASC";
$result_plantas_mes = $conex->query($query_plantas_mes);
$fechas_plantas = [];
$totales_plantas = [];
while($row = $result_plantas_mes->fetch_assoc()) {
    $fechas_plantas[] = $row['fecha'];
    $totales_plantas[] = $row['total'];
}
$total_plantas_mes = array_sum($totales_plantas);

$query_valor = "SELECT SUM(precio_venta * cantidad_disponible) as valor_total,
                       SUM(precio_costo * cantidad_disponible) as costo_total
                FROM inventario";
$result_valor = $conex->query($query_valor);
$valor = $result_valor->fetch_assoc();
$valor_total = $valor['valor_total'] ?? 0;
$costo_total = $valor['costo_total'] ?? 0;
$ganancia_potencial = $valor_total - $costo_total;

$query_usuarios = "SELECT DATE_FORMAT(fecha_contratacion, '%Y-%m') as mes, COUNT(*) as total 
                    FROM usuarios 
                    WHERE fecha_contratacion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                    GROUP BY DATE_FORMAT(fecha_contratacion, '%Y-%m')
                    ORDER BY mes ASC";
$result_usuarios = $conex->query($query_usuarios);
$meses_usuarios = [];
$totales_usuarios = [];
while($row = $result_usuarios->fetch_assoc()) {
    $meses_usuarios[] = $row['mes'];
    $totales_usuarios[] = $row['total'];
}
$total_usuarios = array_sum($totales_usuarios);

$query_tipos = "SELECT tipo_planta, COUNT(*) as total FROM especies GROUP BY tipo_planta";
$result_tipos = $conex->query($query_tipos);
$tipos_labels = [];
$tipos_data = [];
while($row = $result_tipos->fetch_assoc()) {
    $tipos_labels[] = ucfirst($row['tipo_planta']);
    $tipos_data[] = $row['total'];
}

$query_dificultad = "SELECT dificultad_cultivo, COUNT(*) as total FROM especies GROUP BY dificultad_cultivo";
$result_dificultad = $conex->query($query_dificultad);
$dificultad_labels = [];
$dificultad_data = [];
while($row = $result_dificultad->fetch_assoc()) {
    $dificultad_labels[] = ucfirst($row['dificultad_cultivo']);
    $dificultad_data[] = $row['total'];
}

// ========== GENERAR HTML PARA PDF ==========
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
        .header { text-align: center; border-bottom: 3px solid #2d8f6e; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #1a5f4b; margin: 0; font-size: 24px; }
        .header p { color: #666; margin: 5px 0 0 0; font-size: 12px; }
        .section-title { background: #1a5f4b; color: white; padding: 10px 15px; border-radius: 5px; margin: 20px 0 10px 0; font-size: 14px; }
        .stats-grid { display: table; width: 100%; margin-bottom: 20px; }
        .stat-box { display: table-cell; background: #f0f9f4; padding: 15px; border-left: 4px solid #2d8f6e; width: 25%; }
        .stat-box .number { font-size: 22px; font-weight: bold; color: #1a5f4b; }
        .stat-box .label { font-size: 11px; color: #666; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #2d8f6e; color: white; padding: 10px; text-align: left; font-size: 12px; }
        td { padding: 8px 10px; border-bottom: 1px solid #ddd; font-size: 11px; }
        tr:nth-child(even) { background: #f9f9f9; }
        .footer { text-align: center; color: #999; font-size: 11px; margin-top: 40px; border-top: 1px solid #ddd; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🌿 Verde Vida - Reporte de Informes</h1>
        <p>Generado el ' . date('d/m/Y H:i') . ' por ' . $_SESSION['usuario_nombre'] . ' ' . $_SESSION['usuario_apellido'] . '</p>
    </div>

    <div class="section-title">📊 Resumen General</div>
    <div class="stats-grid">
        <div class="stat-box">
            <div class="number">' . $total_plantas_mes . '</div>
            <div class="label">Plantas (último mes)</div>
        </div>
        <div class="stat-box">
            <div class="number">$ ' . number_format($valor_total, 2) . '</div>
            <div class="label">Valor Inventario</div>
        </div>
        <div class="stat-box">
            <div class="number">' . $total_usuarios . '</div>
            <div class="label">Usuarios Registrados</div>
        </div>
        <div class="stat-box">
            <div class="number">$ ' . number_format($ganancia_potencial, 2) . '</div>
            <div class="label">Ganancia Potencial</div>
        </div>
    </div>

    <div class="section-title">🌱 Plantas registradas en el último mes</div>
    <table>
        <thead>
            <tr><th>Fecha</th><th>Cantidad</th></tr>
        </thead>
        <tbody>';

if (count($fechas_plantas) > 0) {
    foreach ($fechas_plantas as $i => $fecha) {
        $html .= '<tr><td>' . date('d/m/Y', strtotime($fecha)) . '</td><td>' . $totales_plantas[$i] . '</td></tr>';
    }
    $html .= '<tr style="background: #d4edda;"><td><strong>TOTAL</strong></td><td><strong>' . $total_plantas_mes . '</strong></td></tr>';
} else {
    $html .= '<tr><td colspan="2" style="text-align:center;">Sin datos en el último mes</td></tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="section-title">👥 Usuarios registrados por mes</div>
    <table>
        <thead>
            <tr><th>Mes</th><th>Cantidad</th></tr>
        </thead>
        <tbody>';

if (count($meses_usuarios) > 0) {
    foreach ($meses_usuarios as $i => $mes) {
        $html .= '<tr><td>' . $mes . '</td><td>' . $totales_usuarios[$i] . '</td></tr>';
    }
    $html .= '<tr style="background: #d4edda;"><td><strong>TOTAL</strong></td><td><strong>' . $total_usuarios . '</strong></td></tr>';
} else {
    $html .= '<tr><td colspan="2" style="text-align:center;">Sin datos de usuarios</td></tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="section-title">🌿 Plantas por tipo</div>
    <table>
        <thead>
            <tr><th>Tipo</th><th>Cantidad</th></tr>
        </thead>
        <tbody>';

if (count($tipos_labels) > 0) {
    foreach ($tipos_labels as $i => $tipo) {
        $html .= '<tr><td>' . $tipo . '</td><td>' . $tipos_data[$i] . '</td></tr>';
    }
} else {
    $html .= '<tr><td colspan="2" style="text-align:center;">Sin datos</td></tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="section-title">⭐ Plantas por dificultad</div>
    <table>
        <thead>
            <tr><th>Dificultad</th><th>Cantidad</th></tr>
        </thead>
        <tbody>';

if (count($dificultad_labels) > 0) {
    foreach ($dificultad_labels as $i => $dif) {
        $html .= '<tr><td>' . $dif . '</td><td>' . $dificultad_data[$i] . '</td></tr>';
    }
} else {
    $html .= '<tr><td colspan="2" style="text-align:center;">Sin datos</td></tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="footer">
        <p>© ' . date('Y') . ' Verde Vida - Reporte generado automáticamente</p>
    </div>
</body>
</html>';

// ========== CONFIGURAR DOMPDF ==========
$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Limpiar cualquier salida previa antes de enviar el PDF
if (ob_get_length()) {
    ob_end_clean();
}

$dompdf->stream('reporte_informes_' . date('Y-m-d') . '.pdf', array('Attachment' => 1));
exit();