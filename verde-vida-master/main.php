<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verde Vida - Inicio</title>
    <style>
        :root {
            --primary-color: #2e7d32;
            --secondary-color: #4caf50;
            --light-color: #e8f5e9;
            --dark-color: #1b5e20;
            --text-color: #333;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8f5e9 100%);
            color: var(--text-color);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: var(--primary-color);
            color: var(--white);
            padding: 1.5rem 0;
            margin-bottom: 40px;
            box-shadow: var(--shadow);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            font-size: 2rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn {
            background: var(--secondary-color);
            color: var(--white);
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn:hover {
            background: var(--dark-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .btn-logout {
            background: #e53935;
        }
        
        .btn-logout:hover {
            background: #c62828;
        }
        
        .welcome-section {
            text-align: center;
            margin-bottom: 50px;
            padding: 40px 20px;
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
        }
        
        .welcome-section h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .welcome-section p {
            color: #666;
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .card {
            background: var(--white);
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 250px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .card h3 {
            margin-bottom: 15px;
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        .card p {
            margin-bottom: 25px;
            color: #666;
            line-height: 1.6;
        }
        
        .card .btn {
            margin-top: auto;
        }
        
        .icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .welcome-section h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <span>🌿</span>
                Verde Vida
            </div>
        <span>
                Hola, <?php 
                    if (isset($_SESSION['usuario_nombre'], $_SESSION['usuario_apellido'])) {
                        echo $_SESSION['usuario_nombre'] . ' ' . $_SESSION['usuario_apellido'];
                    } else {
                        echo "Invitado";
                    }
                ?>
            </span>
            <a href="logout.php" class="btn btn-logout">Cerrar Sesión</a>
        </div>
    </header>
    
    <div class="container">
        <div class="welcome-section">
            <h1>Bienvenido a Verde Vida</h1>
            <p>Gestiona tu inventario de plantas de manera sencilla y organizada</p>
        </div>
        
        <div class="dashboard">
            <div class="card">
                <div class="icon">🌿</div>
                <h3>Registrar Nueva Planta</h3>
                <p>Agrega una nueva planta a tu inventario con toda su información y detalles de cultivo</p>
                <button class="btn" onclick="window.location.href='registrar_plantas.php'">Registrar</button>
            </div>
            
            <div class="card">
                <div class="icon">📋</div>
                <h3>Ver Plantas Registradas</h3>
                <p>Consulta el listado completo de todas tus plantas, edita información o elimina registros</p>
                <button class="btn" onclick="window.location.href='ver_plantas.php'">Ver listado</button>
            </div>
        </div>
    </div>

</body>
</html>