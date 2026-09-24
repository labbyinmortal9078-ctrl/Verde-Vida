<?php
session_start();
include_once("conexion.php");
include_once("permisos.php");
include_once("alertas.php");

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'administrador') {
    header("Location: login.php");
    exit();
}

// Consulta con JOIN para obtener el nombre del usuario
$query = "SELECT a.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido, u.rol as usuario_rol
        FROM auditoria a 
        LEFT JOIN usuarios u ON a.usuario_id = u.ID 
        ORDER BY a.fecha_hora DESC LIMIT 200";
$result = $conex->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría - Verde Vida</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f9f4; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; background: white; border-radius: 24px; padding: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        h1 { color: #1a3e30; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; vertical-align: top; }
        th { background: #f8f9fa; color: #1a3e30; font-weight: 600; }
        tr:hover { background: #f9f9f9; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; white-space: nowrap; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #cce5ff; color: #004085; }
        .badge-purple { background: #e2d4f0; color: #4a148c; }
        .badge-compra { background: #d1ecf1; color: #0c5460; }
        .user-info { display: flex; flex-direction: column; gap: 3px; }
        .user-name { font-weight: 600; color: #1a3e30; }
        .user-email { font-size: 0.75rem; color: #666; }
        .user-role { font-size: 0.7rem; color: #999; font-style: italic; }
        .btn { padding: 8px 16px; background: #2d8f6e; color: white; border: none; border-radius: 8px; text-decoration: none; display: inline-block; margin: 2px; }
        .btn:hover { background: #1a5f4b; }
        .details { font-size: 0.8rem; color: #555; max-width: 400px; word-wrap: break-word; }
        .details strong { color: #1a3e30; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Registro de Auditoría</h1>
        <div style="margin-bottom: 1rem;">
            <a href="main.php" class="btn">← Volver al Inicio</a>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Tabla</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_hora'])); ?></td>
                            <td>
                                <div class="user-info">
                                    <span class="user-name">
                                        <?php 
                                        if ($row['usuario_nombre']) {
                                            echo htmlspecialchars($row['usuario_nombre'] . ' ' . $row['usuario_apellido']);
                                        } else {
                                            echo 'Sistema';
                                        }
                                        ?>
                                    </span>
                                    <span class="user-email"><?php echo htmlspecialchars($row['usuario_email'] ?? ''); ?></span>
                                    <?php if ($row['usuario_rol']): ?>
                                        <span class="user-role"><?php echo ucfirst($row['usuario_rol']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $accion = $row['accion'];
                                $badge = 'badge-info';

                                if (stripos($accion, 'Inicio') !== false)      $badge = 'badge-success';
                                if (stripos($accion, 'Cierre') !== false)      $badge = 'badge-warning';
                                if (stripos($accion, 'Eliminar') !== false)    $badge = 'badge-danger';
                                if (stripos($accion, 'Desactivar') !== false)  $badge = 'badge-danger';
                                if (stripos($accion, 'Activar') !== false)     $badge = 'badge-success';
                                if (stripos($accion, 'Registrar') !== false)   $badge = 'badge-success';
                                if (stripos($accion, 'Cambio') !== false)      $badge = 'badge-purple';
                                if (stripos($accion, 'Compra') !== false)      $badge = 'badge-compra';
                                if (stripos($accion, 'pago') !== false)        $badge = 'badge-compra';
                                if (stripos($accion, 'Venta') !== false)       $badge = 'badge-compra';
                                ?>
                                <span class="badge <?php echo $badge; ?>">
                                    <?php echo htmlspecialchars($accion); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['tabla_afectada'] ?? '-'); ?></td>
                            <td class="details">
                                <?php 
                                $detalle = $row['datos_nuevos'] ?? '';
                                $json = json_decode($detalle, true);

                                if ($json && is_array($json)) {
                                    echo '<strong>Datos:</strong><br>';
                                    foreach ($json as $key => $value) {
                                        $label = ucfirst(str_replace('_', ' ', $key));
                                        if (is_array($value)) {
                                            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                                        }
                                        echo '• ' . htmlspecialchars($label) . ': ' . htmlspecialchars((string)$value) . '<br>';
                                    }
                                } else {
                                    if (!empty($row['datos_anteriores'])) {
                                        echo '<span style="color:#dc3545;">' . htmlspecialchars($row['datos_anteriores']) . '</span>';
                                        echo ' → ';
                                        echo '<span style="color:#28a745;">' . htmlspecialchars($row['datos_nuevos']) . '</span>';
                                    } elseif (!empty($detalle)) {
                                        echo htmlspecialchars($detalle);
                                    } else {
                                        echo '-';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; color:#999; padding:2rem;">
                No hay registros de auditoría
            </p>
        <?php endif; ?>
    </div>
    <?php renderizarAlertas(); ?>
</body>
</html>