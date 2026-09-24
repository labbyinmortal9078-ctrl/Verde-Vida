/* ==========================================================
    SISTEMA DE ALERTAS - VERDE VIDA
    JavaScript para Toastify y SweetAlert2
   ========================================================== */

/**
 * Detecta si el usuario está en un móvil
 */
function esMovil() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) 
            || window.innerWidth <= 768;
}

/**
 * Muestra una notificación flotante con Toastify
 * @param {string} tipo - 'exito' | 'error' | 'advertencia' | 'info'
 * @param {string} mensaje - Mensaje a mostrar
 */
function mostrarNotificacion(tipo, mensaje) {
    const colores = {
        exito:       'linear-gradient(135deg, #2d8f6e 0%, #1a5f4b 100%)',
        success:     'linear-gradient(135deg, #2d8f6e 0%, #1a5f4b 100%)',
        error:       'linear-gradient(135deg, #dc3545 0%, #b02a37 100%)',
        advertencia: 'linear-gradient(135deg, #ffc107 0%, #e0a800 100%)',
        warning:     'linear-gradient(135deg, #ffc107 0%, #e0a800 100%)',
        info:        'linear-gradient(135deg, #17a2b8 0%, #0f7c8e 100%)'
    };
    
    const iconos = {
        exito:       '✅ ',
        success:     '✅ ',
        error:       '❌ ',
        advertencia: '⚠️ ',
        warning:     '⚠️ ',
        info:        'ℹ️ '
    };
    
    const esMobil = esMovil();
    
    Toastify({
        text: (iconos[tipo] || '') + mensaje,
        duration: 4000,
        gravity: "top",
        position: esMobil ? "center" : "right",
        style: {
            background: colores[tipo] || colores.info
        },
        close: true,
        stopOnFocus: true,
        offset: esMobil ? { x: 0, y: 10 } : { x: 20, y: 20 }
    }).showToast();
}

/**
 * Muestra un diálogo de confirmación con SweetAlert2
 * @param {string} titulo - Título del diálogo
 * @param {string} mensaje - Mensaje del diálogo
 * @param {function} accionSi - Función a ejecutar si acepta
 * @param {function} accionNo - Función a ejecutar si cancela (opcional)
 */
function confirmarAccion(titulo, mensaje, accionSi, accionNo = null) {
    Swal.fire({
        title: titulo,
        text: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2d8f6e',
        cancelColor: '#6c757d',
        backdrop: 'rgba(0,0,0,0.4)',
        allowOutsideClick: true,
        width: esMovil() ? '90%' : '420px',
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