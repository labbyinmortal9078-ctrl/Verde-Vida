<?php
// Silenciar warnings para que el JSON salga limpio
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json; charset=utf-8');

include_once("conexion.php");
include_once("carrito_funciones.php");

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success'  => false,
        'message'  => 'Debes iniciar sesión para agregar productos',
        'redirect' => 'login.php'
    ]);
    exit();
}

$usuario_id = (int) $_SESSION['usuario_id'];
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'agregar';

try {
    switch ($accion) {

        // ============================================
        // AGREGAR AL CARRITO
        // ============================================
        case 'agregar':
            $inventario_id = (int) ($_POST['inventario_id'] ?? $_GET['inventario_id'] ?? 0);
            $cantidad      = (int) ($_POST['cantidad'] ?? $_GET['cantidad'] ?? 1);

            if ($inventario_id <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Producto inválido (ID recibido: ' . $inventario_id . ')'
                ]);
                exit();
            }

            if ($cantidad < 1) $cantidad = 1;

            $resultado = agregarAlCarrito($conex, $usuario_id, $inventario_id, $cantidad);

            if (isset($resultado['error'])) {
                echo json_encode(['success' => false, 'message' => $resultado['error']]);
                exit();
            }

            echo json_encode([
                'success'     => true,
                'message'     => '¡Producto agregado al carrito!',
                'total_items' => contarItemsCarrito($conex, $usuario_id)
            ]);
            break;

        // ============================================
        // ACTUALIZAR CANTIDAD
        // ============================================
        case 'actualizar':
            $item_id  = (int) ($_POST['item_id'] ?? 0);
            $cantidad = (int) ($_POST['cantidad'] ?? 1);

            $resultado = actualizarCantidad($conex, $item_id, $usuario_id, $cantidad);

            if (isset($resultado['error'])) {
                echo json_encode(['success' => false, 'message' => $resultado['error']]);
                exit();
            }

            $items = obtenerItemsCarrito($conex, $usuario_id);

            echo json_encode([
                'success'     => true,
                'message'     => 'Cantidad actualizada',
                'total'       => calcularTotal($items),
                'total_items' => contarItemsCarrito($conex, $usuario_id)
            ]);
            break;

        // ============================================
        // ELIMINAR ITEM
        // ============================================
        case 'eliminar':
            $item_id = (int) ($_POST['item_id'] ?? 0);

            $resultado = eliminarItem($conex, $item_id, $usuario_id);

            if (isset($resultado['error'])) {
                echo json_encode(['success' => false, 'message' => $resultado['error']]);
                exit();
            }

            $items = obtenerItemsCarrito($conex, $usuario_id);

            echo json_encode([
                'success'     => true,
                'message'     => 'Producto eliminado',
                'total'       => calcularTotal($items),
                'total_items' => contarItemsCarrito($conex, $usuario_id)
            ]);
            break;

        // ============================================
        // CONTAR ITEMS
        // ============================================
        case 'contar':
            echo json_encode([
                'success'     => true,
                'total_items' => contarItemsCarrito($conex, $usuario_id)
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}

mysqli_close($conex);