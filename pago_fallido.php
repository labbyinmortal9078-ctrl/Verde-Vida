<?php
session_start();
include_once("conexion.php");
include_once("alertas.php");
include_once("carrito_funciones.php");

$total_items_carrito = isset($_SESSION['usuario_id'])
    ? contarItemsCarrito($conex, $_SESSION['usuario_id'])
    : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pago Fallido - Verde Vida</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #dc3545, #c82333);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 3rem;
            color: white;
        }
        h1 { color: #721c24; margin-bottom: 0.5rem; font-size: 1.8rem; }
        p { color: #666; margin-bottom: 2rem; }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #2d8f6e 0%, #1a5f4b 100%);
            color: white;
            transition: all 0.3s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(45,143,110,0.4); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon"><i class="fas fa-times"></i></div>
        <h1>Pago rechazado</h1>
        <p>No pudimos procesar tu pago. Por favor, intenta nuevamente con otro método de pago.</p>
        <a href="carrito.php" class="btn">
            <i class="fas fa-arrow-left"></i> Volver al carrito
        </a>
    </div>
    <?php renderizarAlertas(); ?>
</body>
</html>