<?php
// Datos de conexión para InfinityFree
$servername = "????????";
$username = "??????";
$password = "??????";
$dbname = "??????";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>

