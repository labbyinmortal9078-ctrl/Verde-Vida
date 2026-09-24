<?php
session_start();
include_once("conexion.php");
include_once("permisos.php");
include_once("alertas.php");
include_once("carrito_funciones.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id  = $_SESSION['usuario_id'];
$items       = obtenerItemsCarrito($conex, $usuario_id);
$total       = calcularTotal($items);
$total_items = contarItemsCarrito($conex, $usuario_id);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito - Verde Vida</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f9f4 0%, #d4e8da 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1a3e30;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .tabla-carrito {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        .tabla-carrito th, .tabla-carrito td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        .tabla-carrito th {
            background: #f8f9fa;
            color: #1a3e30;
            font-weight: 600;
        }
        .input-cantidad {
            width: 70px;
            padding: 6px 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            text-align: center;
        }
        .input-cantidad:focus {
            outline: none;
            border-color: #2d8f6e;
        }
        .btn-eliminar {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
        }
        .btn-eliminar:hover { background: #c0392b; }
        .total-container {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
        }
        .total-label { font-size: 1.1rem; color: #666; }
        .total-valor { font-size: 1.8rem; font-weight: 700; color: #1a5f4b; }
        .acciones {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-family: inherit;
            font-size: 0.95rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #2d8f6e 0%, #1a5f4b 100%);
            color: white;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(45,143,110,0.4); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-danger:hover { background: #c0392b; }
        .carrito-vacio {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        .carrito-vacio i { font-size: 4rem; color: #ccc; margin-bottom: 1rem; }
        .carrito-vacio h2 { color: #666; margin-bottom: 0.5rem; }
        .carrito-vacio p { margin-bottom: 2rem; }
        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .tabla-carrito th, .tabla-carrito td { padding: 8px; font-size: 0.85rem; }
            .btn { padding: 10px 16px; font-size: 0.85rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <i class="fas fa-shopping-cart" style="color: #2d8f6e;"></i>
            Mi Carrito
            <span style="font-size: 1rem; color: #6b8f7c; font-weight: 400;">
                (<?php echo $total_items; ?> <?php echo $total_items == 1 ? 'item' : 'items'; ?>)
            </span>
        </h1>

        <?php if (empty($items)): ?>
            <div class="carrito-vacio">
                <i class="fas fa-shopping-basket"></i>
                <h2>Tu carrito está vacío</h2>
                <p>Explora nuestras plantas y añade tus favoritas</p>
                <a href="ver_plantas.php" class="btn btn-primary">
                    <i class="fas fa-seedling"></i> Ver Plantas
                </a>
            </div>
        <?php else: ?>
            <table class="tabla-carrito">
                <thead>
                    <tr>
                        <th>Planta</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($item['nombre_comun']); ?></strong><br>
                            <small style="color:#999;"><?php echo htmlspecialchars($item['nombre_cientifico']); ?></small>
                        </td>
                        <td>$<?php echo number_format($item['precio_unitario'], 2); ?></td>
                        <td>
                            <input type="number"
                                class="input-cantidad"
                                value="<?php echo $item['cantidad']; ?>"
                                min="1"
                                max="<?php echo $item['cantidad_disponible']; ?>"
                                onchange="cambiarCantidad(<?php echo $item['id']; ?>, this.value)">
                        </td>
                        <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                        <td>
                            <button class="btn-eliminar" onclick="quitarItem(<?php echo $item['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total-container">
                <span class="total-label">Total:</span>
                <span class="total-valor" id="total-valor">$<?php echo number_format($total, 2); ?></span>
            </div>

            <div class="acciones">
                <a href="ver_plantas.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Seguir Comprando
                </a>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <button onclick="vaciarCarritoCompleto()" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Vaciar Carrito
                    </button>
                    <a href="procesar_pago.php" class="btn btn-primary">
                        <i class="fas fa-credit-card"></i> Finalizar Compra
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function cambiarCantidad(itemId, cantidad) {
        if (cantidad < 1) return;
        fetch('agregar_carrito.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'accion=actualizar&item_id=' + itemId + '&cantidad=' + cantidad
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-valor').textContent = '$' + data.total.toFixed(2);
                mostrarNotificacion('exito', data.message);
                setTimeout(() => location.reload(), 800);
            } else {
                mostrarNotificacion('error', data.message);
            }
        })
        .catch(() => mostrarNotificacion('error', 'Error al conectar con el servidor'));
    }

    function quitarItem(itemId) {
        confirmarAccion(
            '¿Quitar del carrito?',
            'Este producto se eliminará de tu carrito.',
            function() {
                fetch('agregar_carrito.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'accion=eliminar&item_id=' + itemId
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        mostrarNotificacion('exito', data.message);
                        setTimeout(() => location.reload(), 800);
                    } else {
                        mostrarNotificacion('error', data.message);
                    }
                });
            }
        );
    }

    function vaciarCarritoCompleto() {
        confirmarAccion(
            '⚠️ Vaciar carrito',
            '¿Estás seguro de que quieres eliminar TODOS los productos del carrito?',
            function() {
                window.location.href = 'vaciar_carrito.php';
            }
        );
    }
    </script>

    <?php renderizarAlertas(); ?>
</body>
</html>