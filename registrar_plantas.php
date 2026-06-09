<?php
session_start();
include("conexion.php");
include("permisos.php");

// Verificar permiso para CREAR plantas (registrar)
verificarPermiso($conex, $_SESSION['usuario_rol'], 'registrar_planta', 'crear');

// Redirigir si no está logueado
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
    <title>Registrar Planta - Verde Vida</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f9f4 0%, #d4e8da 100%);
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, #1a5f4b 0%, #0d3b2e 100%);
            padding: 1rem 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.8rem;
            font-weight: 800;
            color: white;
            text-decoration: none;
        }

        .logo i {
            font-size: 2rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            background: rgba(255,255,255,0.15);
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
        }

        .user-info span {
            color: white;
            font-weight: 500;
        }

        .nav-btn {
            background: rgba(255,255,255,0.15);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .logout-btn:hover {
            background: #dc3545;
        }

        /* Main Container */
        .main-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        /* Header */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            color: #1a3e30;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header p {
            color: #666;
            margin-top: 0.5rem;
        }

        /* Form Container */
        .form-container {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0f0e8;
        }

        .form-section h2 {
            color: #1a3e30;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            font-weight: 600;
            color: #1a3e30;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label .required {
            color: #dc3545;
            font-size: 0.8rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s;
            background: #fff;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2d8f6e;
            box-shadow: 0 0 0 3px rgba(45,143,110,0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e0e0e0;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2d8f6e 0%, #1a5f4b 100%);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45,143,110,0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-group.full-width {
                grid-column: span 1;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .main-container {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="main.php" class="logo">
                <i class="fas fa-leaf"></i>
                <span>Verde Vida</span>
            </a>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre'] . ' ' . $_SESSION['usuario_apellido']); ?></span>
                <a href="logout.php" class="nav-btn logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="page-header">
            <h1>
                <i class="fas fa-seedling" style="color: #2d8f6e;"></i>
                Registrar Nueva Planta
            </h1>
            <p>Completa el formulario para agregar una nueva planta al inventario</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="procesar_registro_plantas.php" method="POST">
                <!-- Sección: Información Básica -->
                <div class="form-section">
                    <h2>
                        <i class="fas fa-info-circle"></i>
                        Información Básica
                    </h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-tag"></i>
                                Nombre Común
                                <span class="required">*</span>
                            </label>
                            <input type="text" name="nombre_comun" placeholder="Ej: Rosa, Margarita, Helecho..." required>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-microscope"></i>
                                Nombre Científico
                            </label>
                            <input type="text" name="nombre_cientifico" placeholder="Ej: Rosa spp.">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-tree"></i>
                                Familia
                            </label>
                            <input type="text" name="familia" placeholder="Ej: Rosaceae">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-globe-americas"></i>
                                Origen
                            </label>
                            <input type="text" name="origen" placeholder="Ej: Europa, Asia, América...">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-leaf"></i>
                                Tipo de Planta
                            </label>
                            <select name="tipo_planta">
                                <option value="arbol">🌳 Árbol</option>
                                <option value="arbusto">🌿 Arbusto</option>
                                <option value="hierba">🍃 Hierba</option>
                                <option value="suculenta">🌵 Suculenta</option>
                                <option value="trepadora">🍇 Trepadora</option>
                                <option value="bulbo">🧅 Bulbo</option>
                                <option value="cactus">🌵 Cactus</option>
                                <option value="palmera">🌴 Palmera</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-chart-line"></i>
                                Dificultad de Cultivo
                            </label>
                            <select name="dificultad_cultivo">
                                <option value="baja">🟢 Baja</option>
                                <option value="media">🟡 Media</option>
                                <option value="alta">🔴 Alta</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label>
                                <i class="fas fa-align-left"></i>
                                Descripción
                            </label>
                            <textarea name="descripcion" placeholder="Describe la planta, sus características, cuidados especiales..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Sección: Información de Inventario -->
                <div class="form-section">
                    <h2>
                        <i class="fas fa-boxes"></i>
                        Información de Inventario
                    </h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-sort-numeric-up"></i>
                                Cantidad Disponible
                            </label>
                            <input type="number" name="cantidad_disponible" min="0" value="1">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-chart-simple"></i>
                                Estado de la Planta
                            </label>
                            <select name="estado">
                                <option value="semilla">🌱 Semilla</option>
                                <option value="germinando">🌿 Germinando</option>
                                <option value="plántula">🍃 Plántula</option>
                                <option value="joven" selected>🌳 Joven</option>
                                <option value="madura">🌲 Madura</option>
                                <option value="floracion">🌸 Floración</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-dollar-sign"></i>
                                Precio de Costo ($)
                            </label>
                            <input type="number" name="precio_costo" min="0" step="0.01" value="0.00">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-tag"></i>
                                Precio de Venta ($)
                            </label>
                            <input type="number" name="precio_venta" min="0" step="0.01" value="0.00">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-star"></i>
                                Calidad
                            </label>
                            <select name="calidad">
                                <option value="excelente">⭐ Excelente</option>
                                <option value="regular" selected>👍 Regular</option>
                                <option value="mala">👎 Mala</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-location-dot"></i>
                                Ubicación
                            </label>
                            <input type="text" name="ubicacion" placeholder="Ej: Invernadero A, Estante 3...">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-pen"></i>
                            Notas de Inventario
                        </label>
                        <textarea name="notas" placeholder="Observaciones específicas del inventario..."></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="main.php" class="btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Registrar Planta
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>