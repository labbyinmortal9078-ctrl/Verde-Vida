<?php
if (!function_exists('registrarAuditoria')) {
    function registrarAuditoria($conex, $accion, $tabla = '', $registro_id = null, $datos_anteriores = null, $datos_nuevos = null) {
        $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
        $usuario_email = isset($_SESSION['usuario_email']) ? $_SESSION['usuario_email'] : 'Sistema';
        $usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] . ' ' . ($_SESSION['usuario_apellido'] ?? '') : 'Sistema';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $fecha = date('Y-m-d H:i:s');
        
        $stmt = $conex->prepare("INSERT INTO auditoria 
            (usuario_id, usuario_email, accion, tabla_afectada, registro_id, datos_anteriores, datos_nuevos, ip, fecha_hora) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("issssssss", 
            $usuario_id, 
            $usuario_email, 
            $accion, 
            $tabla, 
            $registro_id, 
            $datos_anteriores, 
            $datos_nuevos, 
            $ip, 
            $fecha
        );
        
        return $stmt->execute();
    }
}
?>