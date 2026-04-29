<?php
session_start();
include("conexion.php");

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