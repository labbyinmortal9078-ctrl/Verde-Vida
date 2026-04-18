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
    <title>Registrar Planta - Verde Vida</title>
    <style>
        
        :root {
            --primary-color: #2e7d32;
            --secondary-color: #4caf50;
            --light-color: #e8f5e9;
            --dark-color: #1b5e20;
            --text-color: #333;
            --white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: var(--text-color);
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 1rem 0;
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .btn {
            background-color: var(--secondary-color);
            color: var(--white);
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1rem;
            margin: 10px;
            display: inline-block;
        }
        
        .btn:hover {
            background-color: var(--dark-color);
        }
        
        .btn-secondary {
            background-color: #95a5a6;
        }
        
        .form-container {
            background-color: var(--white);
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--dark-color);
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-actions {
            text-align: center;
            margin-top: 30px;
        }
        
        .inventario-section {
            border-top: 2px solid var(--primary-color);
            padding-top: 20px;
            margin-top: 20px;
        }
        
        .section-title {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 1.2rem;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">Verde Vida</div>
            <div>
                <span>Hola, <?php echo $_SESSION['usuario_nombre'] . ' ' . $_SESSION['usuario_apellido']; ?></span>
                <a href="logout.php" class="btn btn-logout">Cerrar Sesión</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <h1>Registrar Nueva Planta</h1>
        
        <div class="form-container">
            
            <form action="procesar_registro_plantas.php" method="POST">
                <div class="form-group">
                    <label for="nombre_comun">Nombre Común *</label>
                    <input type="text" id="nombre_comun" name="nombre_comun" required placeholder="Ej: Rosa, Margarita, Helecho...">
                </div>
                
                <div class="form-group">
                    <label for="nombre_cientifico">Nombre Científico</label>
                    <input type="text" id="nombre_cientifico" name="nombre_cientifico" placeholder="Ej: Rosa spp.">
                </div>
                
                <div class="form-group">
                    <label for="familia">Familia</label>
                    <input type="text" id="familia" name="familia" placeholder="Ej: Rosaceae">
                </div>
                
                <div class="form-group">
                    <label for="origen">Origen</label>
                    <input type="text" id="origen" name="origen" placeholder="Ej: Europa, Asia, América...">
                </div>
                
                <div class="form-group">
                    <label for="tipo_planta">Tipo de Planta</label>
                    <select id="tipo_planta" name="tipo_planta">
                        <option value="arbol">Árbol</option>
                        <option value="arbusto">Arbusto</option>
                        <option value="hierba">Hierba</option>
                        <option value="suculenta">Suculenta</option>
                        <option value="trepadora">Trepadora</option>
                        <option value="bulbo">Bulbo</option>
                        <option value="cactus">Cactus</option>
                        <option value="palmera">Palmera</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" placeholder="Describe la planta, cuidados, características..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="dificultad_cultivo">Dificultad de Cultivo</label>
                    <select id="dificultad_cultivo" name="dificultad_cultivo">
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                    </select>
                </div>

                
                <div class="inventario-section">
                    <h3 class="section-title">📦 Información de Inventario</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cantidad_disponible">Cantidad Disponible</label>
                            <input type="number" id="cantidad_disponible" name="cantidad_disponible" min="0" value="1">
                        </div>
                        
                        <div class="form-group">
                            <label for="estado">Estado de la Planta</label>
                            <select id="estado" name="estado">
                                <option value="semilla">Semilla</option>
                                <option value="germinando">Germinando</option>
                                <option value="plántula">Plántula</option>
                                <option value="joven" selected>Joven</option>
                                <option value="madura">Madura</option>
                                <option value="floracion">Floración</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="precio_costo">Precio de Costo ($)</label>
                            <input type="number" id="precio_costo" name="precio_costo" min="0" step="0.01" value="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label for="precio_venta">Precio de Venta ($)</label>
                            <input type="number" id="precio_venta" name="precio_venta" min="0" step="0.01" value="0.00">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="calidad">Calidad</label>
                            <select id="calidad" name="calidad">
                                <option value="excelente">Excelente</option>
                                <option value="regular" selected>Regular</option>
                                <option value="mala">Mala</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="ubicacion">Ubicación</label>
                            <input type="text" id="ubicacion" name="ubicacion" placeholder="Ej: Invernadero A, Estante 3...">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notas">Notas de Inventario</label>
                        <textarea id="notas" name="notas" placeholder="Observaciones específicas del inventario..."></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">🌿 Registrar Planta</button>
                    <a href="main.php" class="btn btn-secondary">↩️ Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>