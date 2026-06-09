<?php
session_start();
include("conexion.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $nombre = mysqli_real_escape_string($conex, $_POST['nombre']);
    $apellido = mysqli_real_escape_string($conex, $_POST['apellido']);
    $email = mysqli_real_escape_string($conex, $_POST['email']);
    $direccion = mysqli_real_escape_string($conex, $_POST['direccion']);
    $telefono = mysqli_real_escape_string($conex, $_POST['telefono']);
    $password = mysqli_real_escape_string($conex, $_POST['password']);
    
    //  Validar campos obligatorios
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
        header("Location: index.php?error=empty");  
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.php?error=email");  
        exit();
    }
    
    // Verificar si el email ya existe
    $sql_check = "SELECT ID FROM usuarios WHERE email = '$email'";
    $result_check = mysqli_query($conex, $sql_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        header("Location: index.php?error=exists"); 
        exit();
    }
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO usuarios (nombre, apellido, email, direccion, telefono, contraseña, rol, fecha_contratacion, hora) 
            VALUES ('$nombre', '$apellido', '$email', '$direccion', '$telefono', '$password_hash', 'jardinero', CURDATE(), NOW())";
    
    if (mysqli_query($conex, $sql)) {
        
        header("Location: login.php?success=registro");
        exit();
    } else {
        echo "Error SQL: " . mysqli_error($conex);
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

mysqli_close($conex);
?>