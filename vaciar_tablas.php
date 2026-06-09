<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}


mysqli_begin_transaction($conex);

try {
    // Primero vaciar inventario
    $sql_inventario = "DELETE FROM inventario";
    if (!mysqli_query($conex, $sql_inventario)) {
        throw new Exception("Error al vaciar inventario: " . mysqli_error($conex));
    }
    
    // Luego vaciar especies
    $sql_especies = "DELETE FROM especies";
    if (!mysqli_query($conex, $sql_especies)) {
        throw new Exception("Error al vaciar especies: " . mysqli_error($conex));
    }
    
    // Reiniciar auto-incremento (opcional)
    mysqli_query($conex, "ALTER TABLE especies AUTO_INCREMENT = 1");
    mysqli_query($conex, "ALTER TABLE inventario AUTO_INCREMENT = 1");
    
    // Confirmar
    mysqli_commit($conex);
    
    echo "<script>
        alert('✅ Todas las plantas han sido eliminadas correctamente');
        window.location.href = 'ver_plantas.php';
    </script>";
    
} catch (Exception $e) {
    
    mysqli_rollback($conex);
    echo "<script>
        alert(' Error: " . addslashes($e->getMessage()) . "');
        window.location.href = 'ver_plantas.php';
    </script>";
}

mysqli_close($conex);
?>