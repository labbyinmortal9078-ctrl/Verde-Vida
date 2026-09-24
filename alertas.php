<?php
/**
 * Sistema de Alertas - Verde Vida
 * Basado en Toastify + SweetAlert2
 * 
 * Uso:
 *   mostrarNotificacion('exito', 'Mensaje');
 *   mostrarNotificacion('error', 'Mensaje');
 *   mostrarNotificacion('advertencia', 'Mensaje');
 *   mostrarNotificacion('info', 'Mensaje');
 */

if (!function_exists('mostrarNotificacion')) {
    function mostrarNotificacion($tipo, $mensaje) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['notificacion'])) {
            $_SESSION['notificacion'] = [];
        }
        
        $_SESSION['notificacion'][] = [
            'tipo' => $tipo,
            'mensaje' => $mensaje
        ];
    }
}

if (!function_exists('renderizarAlertas')) {
    function renderizarAlertas() {
        ?>
        <!-- Toastify CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
        <!-- SweetAlert2 CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <!-- Alertas CSS personalizado -->
        <link rel="stylesheet" href="alertas.css">
        
        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- Alertas JS personalizado -->
        <script src="alertas.js"></script>
        
        <script>
        // ========== NOTIFICACIONES PENDIENTES DESDE PHP ==========
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['notificacion']) && is_array($_SESSION['notificacion'])): ?>
                <?php foreach ($_SESSION['notificacion'] as $index => $notif): ?>
                    setTimeout(function() {
                        mostrarNotificacion(
                            '<?php echo addslashes($notif['tipo']); ?>',
                            '<?php echo addslashes($notif['mensaje']); ?>'
                        );
                    }, <?php echo $index * 300; ?>);
                <?php endforeach; ?>
                <?php unset($_SESSION['notificacion']); ?>
            <?php endif; ?>
        });
        </script>
        <?php
    }
}

if (!function_exists('limpiarNotificaciones')) {
    function limpiarNotificaciones() {
        unset($_SESSION['notificacion']);
    }
}
?>