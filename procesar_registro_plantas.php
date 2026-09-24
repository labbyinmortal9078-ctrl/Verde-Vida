<?php
session_start();
include_once("conexion.php");
include_once("auditoria.php");
include_once("alertas.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['nombre_comun'])) {
    header("Location: registrar_plantas.php");
    exit();
}

// Recoger y limpiar datos
$nombre_comun       = trim($_POST['nombre_comun'] ?? '');
$nombre_cientifico  = trim($_POST['nombre_cientifico'] ?? '');
$familia            = trim($_POST['familia'] ?? '');
$origen             = trim($_POST['origen'] ?? '');
$descripcion        = trim($_POST['descripcion'] ?? '');
$tipo_planta        = trim($_POST['tipo_planta'] ?? 'arbol');
$dificultad_cultivo = trim($_POST['dificultad_cultivo'] ?? 'media');

$cantidad_disponible = (int)   ($_POST['cantidad_disponible'] ?? 0);
$precio_venta        = (float) ($_POST['precio_venta'] ?? 0);
$precio_costo        = (float) ($_POST['precio_costo'] ?? 0);
$estado              = trim($_POST['estado'] ?? 'joven');
$calidad             = trim($_POST['calidad'] ?? 'regular');
$ubicacion           = trim($_POST['ubicacion'] ?? '');
$notas               = trim($_POST['notas'] ?? '');

if ($nombre_comun === '') {
    mostrarNotificacion('error', 'El nombre común es obligatorio');
    header("Location: registrar_plantas.php");
    exit();
}

mysqli_begin_transaction($conex);

try {
    // Insertar en especies
    $sql_especie = "INSERT INTO especies 
        (nombre_comun, nombre_cientifico, familia, origen, tipo_planta, descripcion, dificultad_cultivo, hora)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conex->prepare($sql_especie);
    $stmt->bind_param(
        "sssssss",
        $nombre_comun,
        $nombre_cientifico,
        $familia,
        $origen,
        $tipo_planta,
        $descripcion,
        $dificultad_cultivo
    );

    if (!$stmt->execute()) {
        throw new Exception("Error al insertar en especies: " . $stmt->error);
    }

    $id_especie = $stmt->insert_id;
    $stmt->close();

    // Insertar en inventario
    $sql_inventario = "INSERT INTO inventario 
        (ID_especie, cantidad_disponible, precio_venta, precio_costo, estado, calidad, fecha_ingreso, ubicacion, notas, hora)
        VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, NOW())";

    $stmt2 = $conex->prepare($sql_inventario);
    $stmt2->bind_param(
        "iiddssss",
        $id_especie,
        $cantidad_disponible,
        $precio_venta,
        $precio_costo,
        $estado,
        $calidad,
        $ubicacion,
        $notas
    );

    if (!$stmt2->execute()) {
        throw new Exception("Error al insertar en inventario: " . $stmt2->error);
    }
    $stmt2->close();

    mysqli_commit($conex);

    // Auditoría
    if (function_exists('registrarAuditoria')) {
        registrarAuditoria(
            $conex,
            'Registro de nueva planta',
            'especies',
            $id_especie,
            null,
            json_encode([
                'nombre_comun'        => $nombre_comun,
                'nombre_cientifico'   => $nombre_cientifico,
                'familia'             => $familia,
                'origen'              => $origen,
                'descripcion'         => $descripcion,
                'tipo_planta'         => $tipo_planta,
                'dificultad_cultivo'  => $dificultad_cultivo,
                'cantidad_disponible' => $cantidad_disponible,
                'precio_venta'        => $precio_venta,
                'precio_costo'        => $precio_costo,
                'estado'              => $estado,
                'calidad'             => $calidad,
                'ubicacion'           => $ubicacion,
                'notas'               => $notas
            ])
        );
    }

    mostrarNotificacion('exito', '¡Planta registrada correctamente!');
    header("Location: ver_plantas.php");
    exit();

} catch (Exception $e) {
    mysqli_rollback($conex);
    mostrarNotificacion('error', 'Error al registrar la planta: ' . $e->getMessage());
    header("Location: registrar_plantas.php");
    exit();
} finally {
    mysqli_close($conex);
}