<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

include_once("conexion.php");
include_once("permisos.php");
include_once("auditoria.php");
include_once("alertas.php");
include_once("carrito_funciones.php");

$usuario_id = $_SESSION['usuario_id'];
$mensaje_exito = '';
$mensaje_error = '';

// Contar items del carrito
$total_items_carrito = contarItemsCarrito($conex, $usuario_id);

// Obtener datos actuales del usuario
$query_user = "SELECT nombre, apellido, email FROM usuarios WHERE ID = ?";
$stmt = $conex->prepare($query_user);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Procesar actualización de perfil
if (isset($_POST['update_profile'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $password_nueva = trim($_POST['password_nueva']);
    $password_confirm = trim($_POST['password_confirm']);

    if (empty($nombre) || empty($apellido) || empty($email)) {
        $mensaje_error = "Nombre, apellido y email son obligatorios";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje_error = "Email no válido";
    } else {
        $check_email = "SELECT ID FROM usuarios WHERE email = ? AND ID != ?";
        $stmt = $conex->prepare($check_email);
        $stmt->bind_param("si", $email, $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $mensaje_error = "Este email ya está registrado por otro usuario";
        } else {
            $query = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ? WHERE ID = ?";
            $stmt = $conex->prepare($query);
            $stmt->bind_param("sssi", $nombre, $apellido, $email, $usuario_id);

            if ($stmt->execute()) {
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_apellido'] = $apellido;
                $_SESSION['usuario_email'] = $email;

                if (!empty($password_nueva)) {
                    if (strlen($password_nueva) < 6) {
                        $mensaje_error = "⚠️ La contraseña debe tener al menos 6 caracteres";
                    } elseif ($password_nueva !== $password_confirm) {
                        $mensaje_error = "⚠️ Las contraseñas no coinciden";
                    } else {
                        $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
                        $update_pass = "UPDATE usuarios SET contraseña = ? WHERE ID = ?";
                        $stmt_pass = $conex->prepare($update_pass);
                        $stmt_pass->bind_param("si", $password_hash, $usuario_id);
                        if ($stmt_pass->execute()) {
                            registrarAuditoria($conex, 'Cambiar contraseña', 'usuarios', $usuario_id, null, "Usuario: $nombre $apellido");
                            $mensaje_exito = "✅ Perfil actualizado correctamente";
                        } else {
                            $mensaje_error = "⚠️ Error al actualizar la contraseña";
                        }
                    }
                } else {
                    $mensaje_exito = "✅ Perfil actualizado correctamente";
                }
            } else {
                $mensaje_error = "⚠️ Error al actualizar el perfil";
            }
        }
    }
}

$modulos_permitidos = getModulosPorRol($conex, $_SESSION['usuario_rol']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verde Vida - Panel Principal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f0f9f4 0%, #d4e8da 100%); min-height: 100vh; }

        /* Navbar */
        .navbar { background: linear-gradient(135deg, #1a5f4b 0%, #0d3b2e 100%); padding: 1rem 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000; }
        .nav-container { max-width: 1400px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .logo { display: flex; align-items: center; gap: 12px; font-size: 1.8rem; font-weight: 800; color: white; text-decoration: none; }
        .logo i { font-size: 2rem; }
        .nav-right { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
        .user-info { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.15); padding: 0.5rem 1.2rem; border-radius: 50px; }
        .user-info i { color: #ffd700; font-size: 1.1rem; }
        .user-info span { color: white; font-weight: 500; }
        .nav-btn { background: rgba(255,255,255,0.15); color: white; border: none; padding: 8px 20px; border-radius: 25px; cursor: pointer; font-weight: 600; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; position: relative; }
        .nav-btn:hover { background: rgba(255,255,255,0.3); transform: translateY(-2px); }
        .edit-profile-btn { background: rgba(255,193,7,0.2); border: 1px solid rgba(255,193,7,0.5); }
        .edit-profile-btn:hover { background: #ffc107; color: #1a3e30; }
        .logout-btn:hover { background: #dc3545; }
        .carrito-btn { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
        .carrito-btn:hover { background: linear-gradient(135deg, #e67e22 0%, #d35400 100%); }
        .badge-carrito {
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: 700;
            margin-left: 4px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }

        /* Main Container */
        .main-container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }

        /* Welcome Banner */
        .welcome-banner { background: linear-gradient(135deg, #2d8f6e 0%, #1a5f4b 100%); border-radius: 24px; padding: 2.5rem; color: white; margin-bottom: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: relative; overflow: hidden; }
        .welcome-banner::before { content: "🌿"; position: absolute; right: -20px; bottom: -20px; font-size: 150px; opacity: 0.1; pointer-events: none; }
        .welcome-banner h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        .welcome-banner p { opacity: 0.9; font-size: 1.1rem; }

        /* Cards Grid */
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem; margin-bottom: 2rem; }
        .card { background: white; border-radius: 24px; padding: 2rem; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.08); cursor: pointer; position: relative; overflow: hidden; }
        .card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.15); }
        .card-icon { font-size: 3rem; margin-bottom: 1rem; }
        .card h3 { font-size: 1.5rem; color: #1a3e30; margin-bottom: 0.5rem; }
        .card p { color: #666; line-height: 1.5; margin-bottom: 1.5rem; }
        .card-btn { background: linear-gradient(135deg, #2d8f6e 0%, #1a5f4b 100%); color: white; border: none; padding: 10px 24px; border-radius: 30px; cursor: pointer; font-weight: 600; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .card-btn:hover { transform: scale(1.05); box-shadow: 0 4px 12px rgba(45,143,110,0.4); }
        .card.tienda .card-icon { color: #f39c12; }
        .card.tienda .card-btn { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
        .card.tienda .card-btn:hover { box-shadow: 0 4px 12px rgba(243,156,18,0.4); }

        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center; }
        .modal-content { background: white; border-radius: 24px; padding: 2rem; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; animation: modalFadeIn 0.3s ease; }
        @keyframes modalFadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0f0e8; }
        .modal-header h2 { color: #1a3e30; display: flex; align-items: center; gap: 10px; }
        .close-modal { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999; transition: all 0.3s; }
        .close-modal:hover { color: #dc3545; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1a3e30; display: flex; align-items: center; gap: 8px; }
        .form-group input { width: 100%; padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 12px; font-size: 1rem; transition: all 0.3s; font-family: inherit; }
        .form-group input:focus { outline: none; border-color: #2d8f6e; box-shadow: 0 0 0 3px rgba(45,143,110,0.1); }
        .modal-buttons { display: flex; gap: 1rem; margin-top: 1.5rem; }
        .btn-save { background: linear-gradient(135deg, #2d8f6e 0%, #1a5f4b 100%); color: white; border: none; padding: 12px 24px; border-radius: 40px; cursor: pointer; font-weight: 600; flex: 1; transition: all 0.3s; }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(45,143,110,0.4); }
        .btn-cancel { background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 40px; cursor: pointer; font-weight: 600; flex: 1; transition: all 0.3s; }
        .btn-cancel:hover { background: #5a6268; }

        /* Alerts */
        .alert { padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 500; animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info-text { font-size: 0.8rem; color: #6c757d; margin-top: 0.3rem; }
        hr { margin: 1rem 0; border: none; border-top: 1px solid #e0e0e0; }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-container { flex-direction: column; }
            .nav-right { width: 100%; justify-content: center; }
            .main-container { padding: 0 1rem; }
            .cards-grid { grid-template-columns: 1fr; }
            .welcome-banner h1 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <!-- ========== NAVBAR ========== -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="main.php" class="logo">
                <i class="fas fa-leaf"></i>
                <span>Verde Vida</span>
            </a>
            <div class="nav-right">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['usuario_nombre'] . ' ' . $_SESSION['usuario_apellido']); ?></span>
                </div>

                <!-- Botón Carrito -->
                <a href="carrito.php" class="nav-btn carrito-btn">
                    <i class="fas fa-shopping-cart"></i> Carrito
                    <?php if ($total_items_carrito > 0): ?>
                        <span class="badge-carrito"><?php echo $total_items_carrito; ?></span>
                    <?php endif; ?>
                </a>

                <button class="nav-btn edit-profile-btn" onclick="openProfileModal()">
                    <i class="fas fa-user-edit"></i> Editar Perfil
                </button>
                <a href="logout.php" class="nav-btn logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <!-- ========== MAIN CONTENT ========== -->
    <div class="main-container">
        <div class="welcome-banner">
            <h1>🌱 ¡Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>!</h1>
            <p>Gestiona tu inventario de plantas de manera sencilla y organizada</p>
        </div>

        <!-- ========== CARDS GRID ========== -->
        <div class="cards-grid">

            <!-- Tarjeta: Tienda -->
            <a href="ver_plantas.php" style="text-decoration: none; display: block;">
                <div class="card tienda">
                    <div class="card-icon"><i class="fas fa-store"></i></div>
                    <h3>🛍️ Tienda</h3>
                    <p>Explora las plantas disponibles, añádelas al carrito y realiza tu compra online con Mercado Pago.</p>
                    <button class="card-btn">Comprar ahora <i class="fas fa-arrow-right"></i></button>
                </div>
            </a>

            <?php
            foreach ($modulos_permitidos as $modulo):
                if ($modulo['nombre'] == 'dashboard') continue;
                if ($modulo['nombre'] == 'eliminar_planta') continue;
                if ($modulo['nombre'] == 'exportar') continue;
                if ($modulo['nombre'] == 'perfil') continue;

                $icono = $modulo['icono'];
                $url = $modulo['url'];
                $titulo = $modulo['descripcion'];

                $descripcion = '';
                switch($modulo['nombre']) {
                    case 'usuarios':
                        $descripcion = 'Gestiona los usuarios del sistema: edita roles, activa, desactiva o elimina cuentas.';
                        break;
                    case 'plantas':
                        $descripcion = 'Consulta el listado completo de todas tus plantas, edita información o elimina registros';
                        break;
                    case 'registrar_planta':
                        $descripcion = 'Agrega una nueva planta a tu inventario con toda su información y detalles de cultivo';
                        break;
                    default:
                        $descripcion = $modulo['descripcion'];
                }
            ?>
                <div class="card" onclick="window.location.href='<?php echo $url; ?>'">
                    <div class="card-icon"><i class="fas <?php echo $icono; ?>"></i></div>
                    <h3><?php echo $titulo; ?></h3>
                    <p><?php echo $descripcion; ?></p>
                    <button class="card-btn">Acceder <i class="fas fa-arrow-right"></i></button>
                </div>
            <?php endforeach; ?>

            <!-- Tarjeta: Auditoría (solo admin) -->
            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'administrador'): ?>
                <a href="ver_auditoria.php" style="text-decoration: none; display: block;">
                    <div class="card">
                        <div class="card-icon"><i class="fas fa-clipboard-list"></i></div>
                        <h3>📋 Auditoría</h3>
                        <p>Consulta el registro de todas las acciones del sistema</p>
                        <button class="card-btn">Ver auditoría <i class="fas fa-arrow-right"></i></button>
                    </div>
                </a>
            <?php endif; ?>

            <!-- Tarjeta: Informes y Gráficos (solo admin) -->
            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'administrador'): ?>
                <a href="informes.php" style="text-decoration: none; display: block;">
                    <div class="card">
                        <div class="card-icon"><i class="fas fa-chart-bar"></i></div>
                        <h3>📊 Informes y Gráficos</h3>
                        <p>Estadísticas de plantas, usuarios y valores del inventario</p>
                        <button class="card-btn">Ver informes <i class="fas fa-arrow-right"></i></button>
                    </div>
                </a>
            <?php endif; ?>
        </div>
        <!-- ========== FIN CARDS GRID ========== -->
    </div>

    <!-- ========== MODAL EDITAR PERFIL ========== -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-edit"></i> Editar Mi Perfil</h2>
                <button class="close-modal" onclick="closeProfileModal()">&times;</button>
            </div>

            <?php if ($mensaje_exito): ?>
                <div class="alert alert-success"><?php echo $mensaje_exito; ?></div>
            <?php endif; ?>

            <?php if ($mensaje_error): ?>
                <div class="alert alert-error"><?php echo $mensaje_error; ?></div>
            <?php endif; ?>

            <form method="POST" id="profileForm">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nombre *</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user"></i> Apellido *</label>
                    <input type="text" name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                </div>

                <hr>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Nueva Contraseña <span style="font-weight: normal; font-size: 0.8rem;">(opcional)</span></label>
                    <input type="password" name="password_nueva" id="password_nueva" placeholder="Mínimo 6 caracteres">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-check-circle"></i> Confirmar Contraseña</label>
                    <input type="password" name="password_confirm" id="password_confirm" placeholder="Repite tu nueva contraseña">
                    <div class="info-text" id="passwordMatchHint"></div>
                </div>

                <div class="modal-buttons">
                    <button type="submit" name="update_profile" class="btn-save">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <button type="button" class="btn-cancel" onclick="closeProfileModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========== JAVASCRIPT ========== -->
    <script>
        function openProfileModal() {
            document.getElementById('profileModal').style.display = 'flex';
            document.getElementById('passwordMatchHint').innerHTML = '';
        }

        function closeProfileModal() {
            document.getElementById('profileModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('profileModal');
            if (event.target == modal) {
                closeProfileModal();
            }
        }

        const passwordNueva = document.getElementById('password_nueva');
        const passwordConfirm = document.getElementById('password_confirm');
        const passwordHint = document.getElementById('passwordMatchHint');

        function validatePasswords() {
            const nueva = passwordNueva.value;
            const confirm = passwordConfirm.value;

            if (nueva === '' && confirm === '') {
                passwordHint.innerHTML = '';
                return true;
            }

            if (nueva.length > 0 && nueva.length < 6) {
                passwordHint.innerHTML = '⚠️ La contraseña debe tener al menos 6 caracteres';
                passwordHint.style.color = '#dc3545';
                return false;
            }

            if (nueva !== confirm) {
                passwordHint.innerHTML = '✖ Las contraseñas no coinciden';
                passwordHint.style.color = '#dc3545';
                return false;
            } else if (nueva === confirm && nueva.length >= 6) {
                passwordHint.innerHTML = '✓ Las contraseñas coinciden';
                passwordHint.style.color = '#28a745';
                return true;
            }
            return true;
        }

        passwordNueva.addEventListener('input', validatePasswords);
        passwordConfirm.addEventListener('input', validatePasswords);

        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const nueva = passwordNueva.value;
            const confirm = passwordConfirm.value;

            if (nueva !== '' || confirm !== '') {
                if (nueva.length < 6) {
                    e.preventDefault();
                    passwordHint.innerHTML = '⚠️ La contraseña debe tener al menos 6 caracteres';
                    passwordHint.style.color = '#dc3545';
                    alert('La contraseña debe tener al menos 6 caracteres');
                    return false;
                }
                if (nueva !== confirm) {
                    e.preventDefault();
                    passwordHint.innerHTML = '✖ Las contraseñas no coinciden';
                    passwordHint.style.color = '#dc3545';
                    alert('Las contraseñas no coinciden');
                    return false;
                }
            }
        });
    </script>
</body>
</html>