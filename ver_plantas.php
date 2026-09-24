<?php
session_start();
include_once("conexion.php");
include_once("permisos.php");
include_once("auditoria.php");
include_once("alertas.php");
include_once("carrito_funciones.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

verificarPermiso($conex, $_SESSION['usuario_rol'], 'plantas', 'ver');

// Mensaje flash por GET
if (isset($_GET['registro']) && $_GET['registro'] === 'exitoso') {
    mostrarNotificacion('exito', '¡Planta registrada exitosamente!');
    header("Location: ver_plantas.php");
    exit();
}

// Permisos del usuario actual
$rol             = $_SESSION['usuario_rol'];
$puede_eliminar  = tienePermiso($conex, $rol, 'plantas', 'eliminar');
$puede_crear     = tienePermiso($conex, $rol, 'registrar_planta', 'crear');
$puede_exportar  = tienePermiso($conex, $rol, 'exportar', 'ver');

// Contar items del carrito para el badge
$total_items_carrito = contarItemsCarrito($conex, $_SESSION['usuario_id']);

// Consulta con JOIN + inventario_id
$sql = "SELECT e.ID,
            i.ID AS inventario_id,
            e.nombre_comun, e.nombre_cientifico, e.familia, e.origen,
            e.tipo_planta, e.dificultad_cultivo, e.descripcion,
            i.precio_venta, i.precio_costo, i.cantidad_disponible,
            i.estado, i.calidad, i.ubicacion, i.notas
        FROM especies e
        LEFT JOIN inventario i ON e.ID = i.ID_especie
        ORDER BY e.ID DESC";

$result = mysqli_query($conex, $sql);
$total_plantas = mysqli_num_rows($result);

// Construir filas
$filas_tabla = '';
while ($row = mysqli_fetch_assoc($result)) {
    $acciones = '';

    // Botón Añadir al carrito
    if (!empty($row['inventario_id']) && $row['cantidad_disponible'] > 0 && $row['precio_venta'] > 0) {
        $acciones .= '<button type="button" class="btn-carrito"
                        onclick="agregarAlCarrito(' . (int)$row['inventario_id'] . ', 1)">
                        <i class="fas fa-cart-plus"></i> Añadir
                    </button>';
    } else {
        $acciones .= '<span style="color:#999;font-size:0.75rem;">Sin stock</span>';
    }

    // Botón Eliminar
    if ($puede_eliminar) {
        $id     = (int) $row['ID'];
        $nombre = addslashes($row['nombre_comun']);
        $acciones .= '<button type="button" class="btn-danger"
                        onclick="eliminarPlanta(' . $id . ', \'' . $nombre . '\')">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>';
    }

    $descripcion = !empty($row['descripcion']) ? htmlspecialchars($row['descripcion']) : '-';
    $precio = (isset($row['precio_venta']) && $row['precio_venta'] > 0)
        ? '$ ' . number_format($row['precio_venta'], 2)
        : '-';

    $filas_tabla .= '
    <tr>
        <td>' . htmlspecialchars($row['nombre_comun'])       . '</td>
        <td>' . htmlspecialchars($row['nombre_cientifico'])  . '</td>
        <td>' . htmlspecialchars($row['familia'])            . '</td>
        <td>' . htmlspecialchars($row['origen'])             . '</td>
        <td>' . htmlspecialchars($row['tipo_planta'])        . '</td>
        <td>' . htmlspecialchars($row['dificultad_cultivo']) . '</td>
        <td>' . $descripcion                                 . '</td>
        <td>' . $precio                                      . '</td>
        <td>' . $acciones                                    . '</td>
    </tr>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plantas Registradas - Verde Vida</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0f9f4 0%, #d4e8da 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
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

        .btn-carrito {
            background-color: #2d8f6e;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 8px;
            font-size: 0.8rem;
            cursor: pointer;
            margin: 2px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 600;
        }
        .btn-carrito:hover {
            background-color: #1a5f4b;
            transform: scale(1.02);
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 8px;
            font-size: 0.8rem;
            cursor: pointer;
            margin: 2px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 600;
        }
        .btn-danger:hover {
            background-color: #c0392b;
            transform: scale(1.02);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 0.9rem;
        }

        th {
            background: linear-gradient(135deg, #1a5f4b 0%, #2d8f6e 100%);
            color: white;
            font-weight: 600;
            white-space: nowrap;
        }

        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f0f9f4; }

        .header-actions {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .left-actions, .right-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            background: linear-gradient(135deg, #2d8f6e 0%, #1a5f4b 100%);
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45,143,110,0.4);
        }
        .btn-vaciar { background-color: #e74c3c; }
        .btn-vaciar:hover { background-color: #c0392b; }
        .btn-back { background: #6c757d; }
        .btn-back:hover { background: #5a6268; }

        .btn-carrito-nav {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            position: relative;
        }
        .btn-carrito-nav .badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 7px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .sin-permisos {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .container { padding: 1rem; }
            th, td { padding: 6px; font-size: 0.75rem; white-space: nowrap; }
            .btn, .btn-carrito, .btn-danger { padding: 6px 10px; font-size: 0.7rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <i class="fas fa-seedling" style="color: #2d8f6e;"></i>
            Plantas Registradas
            <span style="font-size: 1rem; color: #6b8f7c; font-weight: 400;">
                (<?php echo $total_plantas; ?> en total)
            </span>
        </h1>

        <div class="header-actions">
            <div class="left-actions">
                <a href="main.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Volver al Inicio
                </a>

                <?php if ($puede_crear): ?>
                    <a href="registrar_plantas.php" class="btn">
                        <i class="fas fa-seedling"></i> Nueva Planta
                    </a>
                <?php endif; ?>

                <?php if ($puede_exportar): ?>
                    <a href="exportar_excel.php" class="btn">
                        <i class="fas fa-file-excel"></i> Exportar a Excel
                    </a>
                <?php endif; ?>
            </div>

            <div class="right-actions">
                <a href="carrito.php" class="btn btn-carrito-nav">
                    <i class="fas fa-shopping-cart"></i> Mi Carrito
                    <?php if ($total_items_carrito > 0): ?>
                        <span class="badge"><?php echo $total_items_carrito; ?></span>
                    <?php endif; ?>
                </a>

                <?php if ($puede_eliminar && $total_plantas > 0): ?>
                    <button onclick="vaciarTablaCompleta()" class="btn btn-vaciar">
                        <i class="fas fa-trash-alt"></i> Vaciar Todo
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($total_plantas > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre Común</th>
                            <th>Nombre Científico</th>
                            <th>Familia</th>
                            <th>Origen</th>
                            <th>Tipo</th>
                            <th>Dificultad</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $filas_tabla; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="sin-permisos">
                <i class="fas fa-info-circle"></i> No hay plantas registradas.
            </div>
        <?php endif; ?>
    </div>

    <script>
    // ============================================================
    // AGREGAR AL CARRITO - VERSIÓN CORREGIDA
    // ============================================================
    function agregarAlCarrito(inventarioId, cantidad) {
        // Asegurar que son números
        inventarioId = parseInt(inventarioId, 10);
        cantidad = parseInt(cantidad, 10) || 1;

        console.log('🛒 Enviando inventario_id =', inventarioId, '| cantidad =', cantidad);

        if (isNaN(inventarioId) || inventarioId <= 0) {
            mostrarNotificacion('error', 'ID de producto inválido: ' + inventarioId);
            return;
        }

        // Construir URL con query string (GET) - más confiable
        const url = 'agregar_carrito.php?accion=agregar'
                + '&inventario_id=' + encodeURIComponent(inventarioId)
                + '&cantidad=' + encodeURIComponent(cantidad);

        fetch(url, {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(response => response.text())
        .then(texto => {
            console.log('📥 Respuesta:', texto);

            // Limpiar posibles comentarios HTML antes del JSON
            const jsonLimpio = texto.replace(/<!--[\s\S]*?-->/g, '').trim();

            let data;
            try {
                data = JSON.parse(jsonLimpio);
            } catch (e) {
                console.error('❌ No es JSON válido. Texto recibido:', texto);
                mostrarNotificacion('error', 'Error del servidor. Revisa la consola.');
                return;
            }

            if (data.success) {
                mostrarNotificacion('exito', data.message);

                // Actualizar badge del carrito
                const nav = document.querySelector('.btn-carrito-nav');
                if (nav) {
                    let badge = nav.querySelector('.badge');
                    if (data.total_items > 0) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'badge';
                            nav.appendChild(badge);
                        }
                        badge.textContent = data.total_items;
                    } else if (badge) {
                        badge.remove();
                    }
                }
            } else {
                mostrarNotificacion('error', data.message);
                if (data.redirect) {
                    setTimeout(() => window.location.href = data.redirect, 1500);
                }
            }
        })
        .catch(err => {
            console.error('❌ Error fetch:', err);
            mostrarNotificacion('error', 'Error al conectar con el servidor');
        });
    }

    // ============================================================
    // ELIMINAR PLANTA
    // ============================================================
    function eliminarPlanta(id, nombre) {
        confirmarAccion(
            '¿Eliminar planta?',
            '¿Estás seguro de que quieres eliminar la planta "' + nombre + '"?\n\nEsta acción no se puede deshacer.',
            function () {
                window.location.href = 'eliminar_planta.php?id=' + id;
            }
        );
    }

    // ============================================================
    // VACIAR INVENTARIO COMPLETO
    // ============================================================
    function vaciarTablaCompleta() {
        confirmarAccion(
            '⚠️ Vaciar inventario',
            'Esto eliminará TODAS las plantas del sistema. Esta acción NO se puede deshacer.\n\n¿Estás seguro?',
            function () {
                confirmarAccion(
                    'Última confirmación',
                    '¿Realmente quieres eliminar TODOS los datos? No hay vuelta atrás.',
                    function () {
                        window.location.href = 'vaciar_tablas.php';
                    }
                );
            }
        );
    }
</script>

<?php renderizarAlertas(); ?>
</body>
</html>