<?php

?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<!-- SweetAlert2 solo para confirmaciones (opcional, más ligero) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Notificación flotante (discreta, desaparece sola)
function mostrarNotificacion(tipo, mensaje) {
    const colores = {
        exito: 'linear-gradient(135deg, #2d8f6e, #1a5f4b)',
        error: 'linear-gradient(135deg, #dc3545, #b02a37)',
        advertencia: 'linear-gradient(135deg, #ffc107, #e0a800)',
        info: 'linear-gradient(135deg, #17a2b8, #0f7c8e)'
    };
    
    Toastify({
        text: mensaje,
        duration: 3000,
        gravity: "top",
        position: "right",
        style: {
            background: colores[tipo] || colores.info,
            borderRadius: "10px",
            padding: "12px 20px",
            fontSize: "14px",
            boxShadow: "0 4px 12px rgba(0,0,0,0.1)"
        },
        close: true
    }).showToast();
}


function confirmarAccion(titulo, mensaje, accionSi, accionNo = null) {
    Swal.fire({
        title: titulo,
        text: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2d8f6e',
        cancelButtonColor: '#6c757d',
        backdrop: false,
        allowOutsideClick: true,
        width: '400px',
        padding: '1.2rem',
        customClass: {
            popup: 'alerta-minimalista'
        }
    }).then((result) => {
        if (result.isConfirmed && accionSi) {
            accionSi();
        } else if (accionNo) {
            accionNo();
        }
    });
}


function desactivarUsuario(id, nombre) {
    confirmarAccion(
        'Desactivar usuario',
        `${nombre} - Baja lógica (puede reactivarse después)`,
        () => window.location.href = `?desactivar=${id}&motivo=Desactivado por administrador`
    );
}

function reactivarUsuario(id, nombre) {
    confirmarAccion(
        'Reactivar usuario',
        `¿Reactivar a ${nombre}? Volverá a tener acceso.`,
        () => window.location.href = `?activar=${id}`
    );
}

function eliminarPermanente(id, nombre) {
    confirmarAccion(
        '⚠️ Eliminar permanentemente',
        `¿Borrar a ${nombre}? Esta acción NO se puede deshacer.`,
        () => window.location.href = `?borrar_permanente=${id}`
    );
}


document.addEventListener('DOMContentLoaded', function() {
    <?php if(isset($_SESSION['notificacion'])): ?>
        mostrarNotificacion(
            '<?php echo $_SESSION['notificacion']['tipo']; ?>',
            '<?php echo addslashes($_SESSION['notificacion']['mensaje']); ?>'
        );
        <?php unset($_SESSION['notificacion']); ?>
    <?php endif; ?>
});
</script>

<style>

.alerta-minimalista {
    border-radius: 16px !important;
    font-family: 'Inter', sans-serif !important;
}
.alerta-minimalista .swal2-title {
    font-size: 1.3rem !important;
    font-weight: 600 !important;
}
.alerta-minimalista .swal2-html-container {
    font-size: 0.9rem !important;
}
.alerta-minimalista .swal2-confirm,
.alerta-minimalista .swal2-cancel {
    padding: 8px 20px !important;
    font-size: 0.85rem !important;
    border-radius: 10px !important;
}
</style>