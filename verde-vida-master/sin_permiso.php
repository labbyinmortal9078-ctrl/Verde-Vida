<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sin Permiso - Verde Vida</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0b3b2f 0%, #1c6e4a 50%, #2d936c 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 25px 45px -12px rgba(0,0,0,0.4);
        }
        .icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
        }
        .btn {
            background: #2d8f6e;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 40px;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #1a5f4b;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🔒</div>
        <h1>Acceso Denegado</h1>
        <p>No tienes permisos suficientes para acceder a esta sección.</p>
        <a href="main.php" class="btn">Volver al Inicio</a>
    </div>
</body>
</html>