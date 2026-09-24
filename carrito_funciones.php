<?php
/**
 * ============================================================
 * CARRITO DE COMPRAS - Verde Vida
 * Funciones para gestionar el carrito persistente en BD
 * ============================================================
 */

// ============================================================
// OBTENER O CREAR CARRITO ACTIVO PARA UN USUARIO
// ============================================================
function obtenerCarrito($conex, $usuario_id) {
    $stmt = $conex->prepare("SELECT id FROM carrito WHERE usuario_id = ? AND estado = 'activo' LIMIT 1");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['id'];
    }
    $stmt->close();

    // Crear carrito nuevo
    $stmt = $conex->prepare("INSERT INTO carrito (usuario_id, estado) VALUES (?, 'activo')");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $carrito_id = $stmt->insert_id;
    $stmt->close();

    return $carrito_id;
}

// ============================================================
// AGREGAR PRODUCTO AL CARRITO
// ============================================================
function agregarAlCarrito($conex, $usuario_id, $inventario_id, $cantidad = 1) {
    $carrito_id = obtenerCarrito($conex, $usuario_id);

    // Obtener precio y stock desde inventario
    $stmt = $conex->prepare("SELECT precio_venta, cantidad_disponible FROM inventario WHERE ID = ? LIMIT 1");
    $stmt->bind_param("i", $inventario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $inv = $result->fetch_assoc();
    $stmt->close();

    if (!$inv) {
        return ['error' => 'Producto no encontrado'];
    }

    if ($inv['cantidad_disponible'] < $cantidad) {
        return ['error' => 'No hay suficiente stock disponible'];
    }

    $precio = $inv['precio_venta'];

    // ¿Ya está en el carrito?
    $stmt = $conex->prepare("SELECT id, cantidad FROM carrito_items WHERE carrito_id = ? AND inventario_id = ?");
    $stmt->bind_param("ii", $carrito_id, $inventario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();

    if ($item) {
        $nueva_cantidad = $item['cantidad'] + $cantidad;
        if ($nueva_cantidad > $inv['cantidad_disponible']) {
            return ['error' => 'No hay suficiente stock para esa cantidad'];
        }
        $stmt = $conex->prepare("UPDATE carrito_items SET cantidad = ? WHERE id = ?");
        $stmt->bind_param("ii", $nueva_cantidad, $item['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conex->prepare("INSERT INTO carrito_items (carrito_id, inventario_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $carrito_id, $inventario_id, $cantidad, $precio);
        $stmt->execute();
        $stmt->close();
    }

    return ['exito' => true];
}

// ============================================================
// ACTUALIZAR CANTIDAD DE UN ITEM
// ============================================================
function actualizarCantidad($conex, $item_id, $usuario_id, $cantidad) {
    if ($cantidad < 1) {
        return eliminarItem($conex, $item_id, $usuario_id);
    }

    // Verificar que el item pertenece al usuario
    $stmt = $conex->prepare("
        SELECT ci.id, i.cantidad_disponible 
        FROM carrito_items ci
        JOIN carrito c ON ci.carrito_id = c.id
        JOIN inventario i ON ci.inventario_id = i.ID
        WHERE ci.id = ? AND c.usuario_id = ? AND c.estado = 'activo'
    ");
    $stmt->bind_param("ii", $item_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();

    if (!$item) {
        return ['error' => 'Item no encontrado'];
    }

    if ($cantidad > $item['cantidad_disponible']) {
        return ['error' => 'No hay suficiente stock'];
    }

    $stmt = $conex->prepare("UPDATE carrito_items SET cantidad = ? WHERE id = ?");
    $stmt->bind_param("ii", $cantidad, $item_id);
    $stmt->execute();
    $stmt->close();

    return ['exito' => true];
}

// ============================================================
// ELIMINAR UN ITEM DEL CARRITO
// ============================================================
function eliminarItem($conex, $item_id, $usuario_id) {
    $stmt = $conex->prepare("
        DELETE ci FROM carrito_items ci
        JOIN carrito c ON ci.carrito_id = c.id
        WHERE ci.id = ? AND c.usuario_id = ? AND c.estado = 'activo'
    ");
    $stmt->bind_param("ii", $item_id, $usuario_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected === 0) {
        return ['error' => 'Item no encontrado'];
    }

    return ['exito' => true];
}

// ============================================================
// OBTENER ITEMS DEL CARRITO
// ============================================================
function obtenerItemsCarrito($conex, $usuario_id) {
    $carrito_id = obtenerCarrito($conex, $usuario_id);

    $sql = "SELECT 
                ci.id,
                ci.cantidad,
                ci.precio_unitario,
                ci.inventario_id,
                e.nombre_comun,
                e.nombre_cientifico,
                i.cantidad_disponible,
                (ci.cantidad * ci.precio_unitario) AS subtotal
            FROM carrito_items ci
            JOIN inventario i ON ci.inventario_id = i.ID
            JOIN especies e ON i.ID_especie = e.ID
            WHERE ci.carrito_id = ?
            ORDER BY ci.id DESC";

    $stmt = $conex->prepare($sql);
    $stmt->bind_param("i", $carrito_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items;
}

// ============================================================
// CALCULAR TOTAL DEL CARRITO
// ============================================================
function calcularTotal($items) {
    $total = 0;
    foreach ($items as $item) {
        $total += $item['precio_unitario'] * $item['cantidad'];
    }
    return $total;
}

// ============================================================
// CONTAR ITEMS DEL CARRITO (para badge en navbar)
// ============================================================
function contarItemsCarrito($conex, $usuario_id) {
    $carrito_id = obtenerCarrito($conex, $usuario_id);

    $stmt = $conex->prepare("SELECT COALESCE(SUM(cantidad), 0) AS total FROM carrito_items WHERE carrito_id = ?");
    $stmt->bind_param("i", $carrito_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return (int) $row['total'];
}

// ============================================================
// VACIAR CARRITO
// ============================================================
function vaciarCarrito($conex, $usuario_id) {
    $carrito_id = obtenerCarrito($conex, $usuario_id);

    $stmt = $conex->prepare("DELETE FROM carrito_items WHERE carrito_id = ?");
    $stmt->bind_param("i", $carrito_id);
    $stmt->execute();
    $stmt->close();

    return true;
}

// ============================================================
// MARCAR CARRITO COMO PAGADO
// ============================================================
function marcarCarritoPagado($conex, $usuario_id) {
    $stmt = $conex->prepare("UPDATE carrito SET estado = 'pagado' WHERE usuario_id = ? AND estado = 'activo'");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->close();
    return true;
}

// ============================================================
// OBTENER O CREAR CLIENTE VINCULADO AL USUARIO
// ============================================================
function obtenerOCrearCliente($conex, $usuario_id) {
    // Buscar cliente vinculado
    $stmt = $conex->prepare("SELECT ID FROM clientes WHERE ID_usuario = ? LIMIT 1");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cliente = $result->fetch_assoc();
    $stmt->close();

    if ($cliente) {
        return $cliente['ID'];
    }

    // Si no existe, crear cliente con datos del usuario
    $stmt = $conex->prepare("
        INSERT INTO clientes (ID_usuario, nombre, apellido, email)
        SELECT ID, nombre, apellido, email FROM usuarios WHERE ID = ?
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $cliente_id = $stmt->insert_id;
    $stmt->close();

    return $cliente_id;
}

// ============================================================
// CREAR VENTA DESDE EL CARRITO
// ============================================================
function crearVentaDesdeCarrito($conex, $usuario_id, $referencia_mp = null) {
    $items = obtenerItemsCarrito($conex, $usuario_id);

    if (empty($items)) {
        return ['error' => 'El carrito está vacío'];
    }

    $cliente_id = obtenerOCrearCliente($conex, $usuario_id);
    $total = calcularTotal($items);

    mysqli_begin_transaction($conex);

    try {
        // 1) Crear la venta
        $stmt = $conex->prepare("
            INSERT INTO ventas (ID_cliente, ID_usuario, fecha_venta, estado, metodo_pago, referencia_mercadopago) 
            VALUES (?, ?, NOW(), 'pendiente', 'mercadopago', ?)
        ");
        $stmt->bind_param("iis", $cliente_id, $usuario_id, $referencia_mp);
        $stmt->execute();
        $venta_id = $stmt->insert_id;
        $stmt->close();

        // 2) Crear detalle de la venta
        $stmt = $conex->prepare("
            INSERT INTO detalle_ventas (ID_venta, ID_inventario, cantidad, subtotal) 
            VALUES (?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            $subtotal = $item['precio_unitario'] * $item['cantidad'];
            $stmt->bind_param("iiid", $venta_id, $item['inventario_id'], $item['cantidad'], $subtotal);
            $stmt->execute();
        }
        $stmt->close();

        mysqli_commit($conex);

        return [
            'exito'    => true,
            'venta_id' => $venta_id,
            'total'    => $total,
            'cliente_id' => $cliente_id
        ];

    } catch (Exception $e) {
        mysqli_rollback($conex);
        return ['error' => 'Error al crear venta: ' . $e->getMessage()];
    }
}

// ============================================================
// DESCONTAR STOCK DESPUÉS DEL PAGO
// ============================================================
function descontarStock($conex, $venta_id) {
    $stmt = $conex->prepare("
        UPDATE inventario i
        JOIN detalle_ventas dv ON i.ID = dv.ID_inventario
        SET i.cantidad_disponible = i.cantidad_disponible - dv.cantidad
        WHERE dv.ID_venta = ?
    ");
    $stmt->bind_param("i", $venta_id);
    $stmt->execute();
    $stmt->close();
    return true;
}
?>