<?php
session_start();
include("conexion.php");

if (isset($_POST['login'])) {
    if (isset($_POST['email']) && isset($_POST['password'])){
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        $consulta = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conex->prepare($consulta);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0){
            $usuario = $resultado->fetch_assoc();
        }

        if (password_verify($password, $usuario['contraseña'])) {
            $_SESSION['usuario_id'] = $usuario['ID'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_apellido'] = $usuario['apellido']; 
            $_SESSION['usuario_email'] = $usuario['email'];
            header("Location: main.php");
            exit();
        } else {
            echo "<script>alert('Email o contraseña incorrectos');</script>";
        }
    } else {
        echo "<script>alert('Usuario no encontrado');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Verde Vida</title>
    <style>
        body { font-family: Arial; margin: 40px; background: #f5f5f5; }
        .form-container { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
        .btn { background: #4caf50; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Iniciar Sesión - Verde Vida</h2>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="submit" name="login" value="Iniciar Sesión" class="btn">
        </form>
        <p style="text-align: center; margin-top: 15px;">
        
        </p>
    </div>
    <div style="text-align: center; margin-top: 20px;">
    <p>¿No tienes cuenta? <a href="index.php">Regístrate aquí</a></p>
</div>
</body>
</html>