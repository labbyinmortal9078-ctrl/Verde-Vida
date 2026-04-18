<?php
if (isset($_POST['register'])){
    include("conexion.php");
    
    if(
        isset($_POST['name']) && strlen($_POST['name']) >= 3 &&
        isset($_POST['apellido']) && strlen($_POST['apellido']) >= 3 &&
        isset($_POST['email']) && strlen($_POST['email']) >= 3 &&
        isset($_POST['direccion']) && strlen($_POST['direccion']) >= 3 &&
        isset($_POST['phone']) && strlen($_POST['phone']) >= 3 &&
        isset($_POST['password']) && strlen($_POST['password']) >= 1
    ){
        $name = trim($_POST['name']);
        $apellido = trim($_POST['apellido']);
        $email = trim($_POST['email']);
        $direccion = trim($_POST['direccion']);
        $phone = trim($_POST['phone']);
        $password = trim($_POST['password']);
        $fecha = date("d/m/y");
        
        $consulta = "INSERT INTO usuarios(nombre, apellido, email, contraseña, telefono, direccion, fecha_contratacion) 
                    VALUES('$name','$apellido','$email','$password','$phone','$direccion','$fecha')";
        
        $resultado = mysqli_query($conex,$consulta);
        
        if ($resultado){
            
            echo "<script>
                alert('✅ Registro exitoso. Serás redirigido al login.');
                window.location.href = 'login.php';
            </script>";
            exit(); 
        } else {
            echo "<div style='color: red; text-align: center;'>❌ Error: " . mysqli_error($conex) . "</div>";
        }
    } else {
        echo "<div style='color: red; text-align: center;'>⚠️ Por favor, completa todos los campos</div>";
    }
}
?>