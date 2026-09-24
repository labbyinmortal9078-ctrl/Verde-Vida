<?php
session_start();
include_once("conexion.php");
include_once("permisos.php");
include_once("auditoria.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Verificar que se recibió el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>
        alert('❌ Error: No se especificó la planta a eliminar');
        window.location.href = 'ver_plantas.php';
    </script>";
    exit();
}

$planta_id = intval($_GET['id']);

// ========== OBTENER DATOS DE LA PLANTA ANTES DE ELIMINAR ==========
$query_info = "SELECT e.nombre_comun, e.nombre_cientifico, i.precio_venta 
               FROM especies e 
               LEFT JOIN inventario i ON e.ID = i.ID_especie 
               WHERE e.ID = ?";
$stmt_info = $conex->prepare($query_info);
$stmt_info->bind_param("i", $planta_id);
$stmt_info->execute();
$result_info = $stmt_info->get_result();
$planta_data = $result_info->fetch_assoc();

$nombre_planta = $planta_data['nombre_comun'] ?? 'Desconocida';
$nombre_cientifico = $planta_data['nombre_cientifico'] ?? '';
$precio_planta = $planta_data['precio_venta'] ?? 0;
// ================================================================

mysqli_begin_transaction($conex);

try {
    // 1. Primero eliminar de inventario (por la clave foránea)
    $sql_inventario = "DELETE FROM inventario WHERE ID_especie = $planta_id";
    if (!mysqli_query($conex, $sql_inventario)) {
        throw new Exception("Error al eliminar del inventario: " . mysqli_error($conex));
    }
    
    // 2. Luego eliminar de especies
    $sql_especies = "DELETE FROM especies WHERE ID = $planta_id";
    if (!mysqli_query($conex, $sql_especies)) {
        throw new Exception("Error al eliminar la especie: " . mysqli_error($conex));
    }
    
    mysqli_commit($conex);
    
    // ========== REGISTRAR AUDITORÍA ==========
    registrarAuditoria(
        $conex,
        'Eliminar planta',
        'especies',
        $planta_id,
        null,
        "Planta eliminada: $nombre_planta ($nombre_cientifico) - Precio: $$precio_planta"
    );
    // =========================================
    
    // Éxito - redirigir con mensaje
    echo "<script>
        alert('✅ Planta eliminada correctamente');
        window.location.href = 'ver_plantas.php';
    </script>";
    
} catch (Exception $e) {
    
    mysqli_rollback($conex);
    echo "<script>
        alert('❌ Error: " . addslashes($e->getMessage()) . "');
        window.location.href = 'ver_plantas.php';
    </script>";
}

mysqli_close($conex);
?>