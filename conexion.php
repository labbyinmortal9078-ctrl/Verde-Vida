<?php
$servidor = "localhost";
$usuario = "root";
$password = "Flashpoint2025#"; 
$basedatos = "vivero4";


$conex = mysqli_connect("localhost", "root", "Flashpoint2025#", "vivero4");
if ($conex) {
    echo "Error: " . mysqli_connect_error();
}


?>