<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['nombre_comun'])) {
    
    $nombre_comun = mysqli_real_escape_string($conex, $_POST['nombre_comun']);
    $nombre_cientifico = mysqli_real_escape_string($conex, $_POST['nombre_cientifico']);
    $familia = mysqli_real_escape_string($conex, $_POST['familia']);
    $origen = mysqli_real_escape_string($conex, $_POST['origen']);
    $descripcion = mysqli_real_escape_string($conex, $_POST['descripcion']);
    $tipo_planta = mysqli_real_escape_string($conex, $_POST['tipo_planta']);
    $dificultad_cultivo = mysqli_real_escape_string($conex, $_POST['dificultad_cultivo']);
    
    $cantidad_disponible = mysqli_real_escape_string($conex, $_POST['cantidad_disponible']);
    $precio_venta = mysqli_real_escape_string($conex, $_POST['precio_venta']);
    $precio_costo = mysqli_real_escape_string($conex, $_POST['precio_costo']);
    $estado = mysqli_real_escape_string($conex, $_POST['estado']);
    $calidad = mysqli_real_escape_string($conex, $_POST['calidad']);
    $ubicacion = mysqli_real_escape_string($conex, $_POST['ubicacion']);
    $notas = mysqli_real_escape_string($conex, $_POST['notas']);
    
    mysqli_begin_transaction($conex);
    
    try {
        
        $sql_especie = "INSERT INTO especies 
                (nombre_comun, nombre_cientifico, familia, origen, tipo_planta, descripcion, dificultad_cultivo, hora) 
                VALUES 
                ('$nombre_comun', '$nombre_cientifico', '$familia', '$origen', '$tipo_planta', '$descripcion', '$dificultad_cultivo', NOW())";
        
        if (!mysqli_query($conex, $sql_especie)) {
            throw new Exception("Error al insertar en especies: " . mysqli_error($conex));
        }
        
        $id_especie = mysqli_insert_id($conex);
        
        $sql_inventario = "INSERT INTO inventario 
                (ID_especie, cantidad_disponible, precio_venta, precio_costo, estado, calidad, fecha_ingreso, ubicacion, notas, hora) 
                VALUES 
                ('$id_especie', '$cantidad_disponible', '$precio_venta', '$precio_costo', '$estado', '$calidad', CURDATE(), '$ubicacion', '$notas', NOW())";
        
        if (!mysqli_query($conex, $sql_inventario)) {
            throw new Exception("Error al insertar en inventario: " . mysqli_error($conex));
        }
        
        mysqli_commit($conex);
        
        header("Location: ver_plantas.php?registro=exitoso");
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($conex);
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: registrar_plantas.php");
    exit();
}

mysqli_close($conex);
?>