<?php
include_once("conexion.php");
session_start();
// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// ========== CONSULTAS ==========
// 1. Plantas registradas en el último mes
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

// 2. Valor del inventario
$query_valor = "SELECT SUM(precio_venta * cantidad_disponible) as valor_total,
                       SUM(precio_costo * cantidad_disponible) as costo_total
                FROM inventario";
$result_valor = $conex->query($query_valor);
$valor = $result_valor->fetch_assoc();
$valor_total = $valor['valor_total'] ?? 0;
$costo_total = $valor['costo_total'] ?? 0;
$ganancia_potencial = $valor_total - $costo_total;

// 3. Usuarios registrados por mes
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

// 4. Plantas por tipo
$query_tipos = "SELECT tipo_planta, COUNT(*) as total FROM especies GROUP BY tipo_planta";
$result_tipos = $conex->query($query_tipos);
$tipos_labels = [];
$tipos_data = [];
while($row = $result_tipos->fetch_assoc()) {
    $tipos_labels[] = ucfirst($row['tipo_planta']);
    $tipos_data[] = $row['total'];
}

// 5. Plantas por dificultad
$query_dificultad = "SELECT dificultad_cultivo, COUNT(*) as total FROM especies GROUP BY dificultad_cultivo";
$result_dificultad = $conex->query($query_dificultad);
$dificultad_labels = [];
$dificultad_data = [];
while($row = $result_dificultad->fetch_assoc()) {
    $dificultad_labels[] = ucfirst($row['dificultad_cultivo']);
    $dificultad_data[] = $row['total'];
}

// ========== GENERAR EXCEL ==========
$nombre_archivo = "reporte_informes_" . date('Y-m-d') . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF"; // BOM UTF-8 para acentos

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th { background: #2d8f6e; color: white; padding: 8px; border: 1px solid #ddd; text-align: left; }
        td { padding: 6px 8px; border: 1px solid #ddd; }
        .title { font-size: 18px; font-weight: bold; background: #1a5f4b; color: white; padding: 10px; }
        .subtitle { font-size: 14px; font-weight: bold; background: #d4edda; padding: 8px; }
        .stat-label { font-weight: bold; background: #f0f9f4; }
        .stat-value { text-align: right; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="4" class="title">🌿 Verde Vida - Reporte de Informes</td>
        </tr>
        <tr>
            <td colspan="4">Fecha de generación: <?php echo date('d/m/Y H:i'); ?></td>
        </tr>
        <tr>
            <td colspan="4">Usuario: <?php echo $_SESSION['usuario_nombre'] . ' ' . $_SESSION['usuario_apellido']; ?></td>
        </tr>
        <tr><td colspan="4"></td></tr>

        <!-- RESUMEN GENERAL -->
        <tr>
            <td colspan="4" class="subtitle">📊 RESUMEN GENERAL</td>
        </tr>
        <tr>
            <td class="stat-label">Total Plantas (último mes)</td>
            <td class="stat-value"><?php echo $total_plantas_mes; ?></td>
            <td class="stat-label">Total Usuarios (últimos 6 meses)</td>
            <td class="stat-value"><?php echo $total_usuarios; ?></td>
        </tr>
        <tr>
            <td class="stat-label">Costo Total Inventario</td>
            <td class="stat-value">$ <?php echo number_format($costo_total, 2); ?></td>
            <td class="stat-label">Valor de Venta Inventario</td>
            <td class="stat-value">$ <?php echo number_format($valor_total, 2); ?></td>
        </tr>
        <tr>
            <td class="stat-label">Ganancia Potencial</td>
            <td class="stat-value">$ <?php echo number_format($ganancia_potencial, 2); ?></td>
            <td colspan="2"></td>
        </tr>
        <tr><td colspan="4"></td></tr>

        <!-- PLANTAS DEL ÚLTIMO MES -->
        <tr>
            <td colspan="4" class="subtitle">🌱 PLANTAS REGISTRADAS EN EL ÚLTIMO MES</td>
        </tr>
        <tr>
            <th>Fecha</th>
            <th>Cantidad</th>
        </tr>
        <?php foreach ($fechas_plantas as $i => $fecha): ?>
        <tr>
            <td><?php echo date('d/m/Y', strtotime($fecha)); ?></td>
            <td class="stat-value"><?php echo $totales_plantas[$i]; ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td><strong>TOTAL</strong></td>
            <td class="stat-value"><strong><?php echo $total_plantas_mes; ?></strong></td>
        </tr>
        <tr><td colspan="4"></td></tr>

        <!-- USUARIOS POR MES -->
        <tr>
            <td colspan="4" class="subtitle">👥 USUARIOS REGISTRADOS POR MES</td>
        </tr>
        <tr>
            <th>Mes</th>
            <th>Cantidad</th>
        </tr>
        <?php foreach ($meses_usuarios as $i => $mes): ?>
        <tr>
            <td><?php echo $mes; ?></td>
            <td class="stat-value"><?php echo $totales_usuarios[$i]; ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td><strong>TOTAL</strong></td>
            <td class="stat-value"><strong><?php echo $total_usuarios; ?></strong></td>
        </tr>
        <tr><td colspan="4"></td></tr>

        <!-- PLANTAS POR TIPO -->
        <tr>
            <td colspan="4" class="subtitle">🌿 PLANTAS POR TIPO</td>
        </tr>
        <tr>
            <th>Tipo</th>
            <th>Cantidad</th>
        </tr>
        <?php foreach ($tipos_labels as $i => $tipo): ?>
        <tr>
            <td><?php echo $tipo; ?></td>
            <td class="stat-value"><?php echo $tipos_data[$i]; ?></td>
        </tr>
        <?php endforeach; ?>
        <tr><td colspan="4"></td></tr>

        <!-- PLANTAS POR DIFICULTAD -->
        <tr>
            <td colspan="4" class="subtitle">⭐ PLANTAS POR DIFICULTAD</td>
        </tr>
        <tr>
            <th>Dificultad</th>
            <th>Cantidad</th>
        </tr>
        <?php foreach ($dificultad_labels as $i => $dif): ?>
        <tr>
            <td><?php echo $dif; ?></td>
            <td class="stat-value"><?php echo $dificultad_data[$i]; ?></td>
        </tr>
        <?php endforeach; ?>
        <tr><td colspan="4"></td></tr>

        <tr>
            <td colspan="4" style="text-align: center; color: #666; font-size: 11px;">
                © <?php echo date('Y'); ?> Verde Vida - Reporte generado automáticamente
            </td>
        </tr>
    </table>
</body>
</html>